<?php

namespace App\Admin\Forms;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectGrade;
use App\Models\UserRelCategory;
use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Auth;

class ProjectGradeEditFormByUncomment extends Form implements LazyRenderable
{
    use LazyWidget;

    // 使用异步加载功能

    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        // 获取外部传递参数
        $id = $this->payload['id'] ?? null;

        // 表单参数
        $grade = $input['grade'] ?? null;
        $note = $input['note'] ?? '';

        if (!$id) {
            return $this->response()->error('参数错误');
        }

        $project = Project::query()
            ->where('id', $id)
            ->first();

        $category = Category::query()->where(['id' => $project->category_id])->first();
        if ($category->min_grade > $grade || $category->max_grade < $grade) {
            return $this->response()->error("请在 $category->min_grade 至 $category->max_grade 的区间内进行评分");

        }
        $userCategory = UserRelCategory::query()
            ->where([
                'user_id' => Admin::user()->id,
                'category_id' => $project->category_id
            ])->first();
        if (!$userCategory) {
            return $this->response()->error("无权限打分");
        }

        $pg = ProjectGrade::query()
            ->firstOrNew(['project_id' => $project->id, 'user_judge_id' => Admin::user()->id], [
                'project_id' =>$project->id
                , 'category_id' => $project->category_id
                , 'user_judge_id' => Admin::user()->id
                , 'grade' => $grade
                , 'note' => $note
            ]);
        if ($pg->id) {
            return $this->response()->error("该项目已打分，不可重复打分");
        }
        $project->status = 1;
        $pg->save();
        $project->save();

        return $this->response()->success('操作成功')->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->decimal('grade', '成绩')->required();
        $this->textarea('note', '意见');
    }

    /**
     * The data of the form.
     *
     * @return array
     */
//    public function default()
//    {
//        $id = $this->payload['id'] ?? null;
//        $m = ProjectGrade::query()->find($id);
//
//        return [
//            'grade' => $m->grade,
//            'note' => $m->note,
//        ];
//    }
}
