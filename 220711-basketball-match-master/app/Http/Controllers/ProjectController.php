<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectGrade;
use App\Models\UserRelCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectController extends BaseController
{

    public function __construct()
    {
        $this->middleware('api.auth', ['except' => []]);
        parent::__construct();

    }

    //报名列表
    public function index(Request $request)
    {
        $q = \App\Models\Project::query()->with(['category']);

        if (Auth::user()->role_type == 1) {
            //普通用户
            $q->where('user_id', Auth::user()->id);
        } else {
            $categories = UserRelCategory::query()->where('user_id',Auth::user()->id)->pluck('category_id');
            //评委
            if($request->get('judge_commented') == 1){
                $q->whereRaw('exists(select 1 from project_grade pg where user_judge_id = ? and project.id = pg.project_id)', Auth::user()->id);
            } else {
                $q->whereRaw('not exists(select 1 from project_grade pg where user_judge_id = ? and project.id = pg.project_id)', Auth::user()->id);
            }
            $q->whereIn('category_id',$categories);
        }

        if ($request->get('category_id')) {
            $q->where('category_id', $request->get('category_id'));
        }

        $list = $q
            ->select([
                'project.*'
                , DB::raw('(select not exists(select 1 from project_grade pg where pg.project_id = project.id)) as tf_edit')
            ])
            ->orderByDesc('id')
            ->paginate($request->get('pageSize'));

        return $this->successPage($list);
    }

    public function show(Request $request)
    {
        $q = \App\Models\Project::query();

        $list = $q
            ->with(['ProjectGrade','ProjectGrade.user'])
            ->select([
                'project.*'
                , DB::raw('(select CAST(AVG(grade) AS DECIMAL(10,2)) from project_grade pg where pg.project_id = project.id) as avg_grade')
                , DB::raw('(select min(grade) from project_grade pg where pg.project_id = project.id) as min_grade')
                , DB::raw('(select max(grade) from project_grade pg where pg.project_id = project.id) as max_grade')
                , DB::raw('(select not exists(select 1 from project_grade pg where pg.project_id = project.id)) as tf_edit')
            ])
            ->selectRaw('(select 1 from project_grade pg where user_judge_id = ? and project.id = pg.project_id limit 1) as is_commented', [Auth::user()->id])
            ->where(['id'=>$request->get('id')])
            ->first();

        return $this->success($list);
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_name' => 'required|string',
//            'mobile' => 'required',
            'person_cnt' => 'required',
            'school_name' => 'required',
            'level' => 'required',
            'teacher_name' => 'required|string',
            'tf_creator' => 'required',
            'category_id' => 'required',
            'video_path' => 'required',
        ], [
            'product_name.required' => '节目名称必填',
            'school_name.required' => '学校名称必填',
//            'mobile.required' => '手机号必填',
            'person_cnt.required' => '参赛人数必填',
            'level.required' => '组别必填',
            'teacher_name.required' => '指导老师必填',
            'tf_creator.required' => '是否原创必选',
            'category_id.required' => '报名类型必选',
            'video_path.required' => '视频地址必传',
            'class_name.required' => '班级必传必传',
        ]);
        if ($valid->fails()) {
            return $this->error_400($valid->errors()->first());
        }

        $data = array_filter($request->all([
            'product_name',
            'school_name',
            'person_cnt',
            'level',
            'category_id',
            'mobile',
            'teacher_name',
            'show_time',
            'tf_creator',
            'creator_name',
            'video_path',
            'class_name',
        ]));

        if ($request->get('tf_creator')==1 && empty($request->get('creator_name'))){
            return $this->error_400('当为原创作品时，作者名称必传');
        }
        $project = Project::where('id', $request->get('id'))
            ->where('user_id', Auth::user()->id)
            ->first();
        if ($project) {
            if ($project->status == 1) {
                return $this->error_400('已开始被评审，不可修改');
            }
        } else {
            $data['user_id'] = Auth::user()->id;
        }

        $res = Project::query()
            ->updateOrCreate([
                'user_id' => Auth::user()->id,
                'id' => $request->get('id'),
            ], $data);
        $res->save();
        return $this->success([]);
    }

    public function delete(Request $request){
        $valid = Validator::make($request->all(), [
            'id' => 'required',
        ], [
            'id.required' => '项目ID必传',
        ]);
        if ($valid->fails()) {
            return $this->error_400($valid->errors()->first());
        }

        $project = Project::query()
            ->where('id', $request->get('id'))
            ->where('user_id', Auth::user()->id)
            ->first();
        if (!$project) {
            return $this->error_400('未找到报名数据');
        }
        $filePath = str_replace('https://api.hjsn101.top/','',str_replace('http://www.hjsn101.top/','',$project->video_path));
        if (env('APP_URL')!=""){
            $filePath = str_replace(env('APP_URL'),'',$filePath);
        }
        $filePath = ltrim($filePath,'/');
        if (Storage::disk('admin')->exists($filePath)){
            Storage::disk('admin')->delete($filePath);
        }
        $project->delete();
        ProjectGrade::query()->where(['project_id'=>$project->id])->delete();

        return $this->success([]);
    }

    public function grade(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'grade' => 'required',
        ], [
            'id.required' => '项目ID必传',
            'grade.required' => '成绩必传',
        ]);
        if ($valid->fails()) {
            return $this->error_400($valid->errors()->first());
        }

        $project = Project::query()
            ->where('id', $request->get('id'))
            ->first();
        if (!$project) {
            return $this->error_400('未找到报名数据');
        }

        $category = Category::query()->where(['id' => $project->category_id])->first();
        if ($category->min_grade > $request->get('grade') || $category->max_grade < $request->get('grade')) {
            return $this->error_400("请在 $category->min_grade 至 $category->max_grade 的区间内进行评分");
        }
        $userCategory = UserRelCategory::query()
            ->where([
                'user_id' => Auth::user()->id,
                'category_id' => $project->category_id
            ])->first();
        if (!$userCategory) {
            return $this->error_400('无权限打分');
        }

        $pg = ProjectGrade::query()
            ->firstOrNew(['project_id' => $request->get('id'), 'user_judge_id' => Auth::user()->id], [
                'project_id' =>$project->id
                , 'category_id' => $project->category_id
                , 'user_judge_id' => Auth::user()->id
                , 'grade' => $request->get('grade')
                , 'note' => $request->get('note')
            ]);
        if ($pg->id) {
            return $this->error_400('该项目已打分，不可重复打分');
        }
        $project->status = 1;
        $project->save();
        $pg->save();
        return $this->success();
    }
}
