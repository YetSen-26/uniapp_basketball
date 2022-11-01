<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContractPlayer;
use App\Models\Goods;
use App\Models\Ranking;
use App\Models\RankingPerson;
use App\Models\RankingPersonGrade;
use App\Models\UserGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{

    public function __construct()
    {
        $this->middleware('api.auth', ['except' => []]);
        parent::__construct();

    }

    //场均数据
    public function avgData(Request $request)
    {
        $q = UserGrade::query();

        $list = $q
            ->select([
                DB::raw("count(1) AS total_cnt"),

                DB::raw("ROUND(AVG(total_pointer),1) AS avg_pointer"),
                DB::raw("ROUND(AVG(two_pointer),1) AS avg_two_pointer"),
                DB::raw("ROUND(AVG(three_pointer),1) AS avg_three_pointer"),
                DB::raw("ROUND(AVG(four_pointer),1) AS avg_four_pointer"),
                DB::raw("ROUND(AVG(penalty_cnt),1) AS avg_penalty_cnt"),
                DB::raw("ROUND(AVG(rebound_cnt),1) AS avg_rebound_cnt"),
                DB::raw("ROUND(AVG(assist_cnt),1) AS avg_assist_cnt"),

                DB::raw("SUM(total_pointer) AS sum_pointer"),
                DB::raw("SUM(two_pointer) AS sum_two_pointer"),
                DB::raw("SUM(three_pointer) AS sum_three_pointer"),
                DB::raw("SUM(four_pointer) AS sum_four_pointer"),
                DB::raw("SUM(penalty_cnt) AS sum_penalty_cnt"),
                DB::raw("SUM(rebound_cnt) AS sum_rebound_cnt"),
                DB::raw("SUM(assist_cnt) AS sum_assist_cnt"),

                DB::raw("max(total_pointer) AS max_pointer"),
                DB::raw("MAX(two_pointer) AS max_two_pointer"),
                DB::raw("MAX(three_pointer) AS max_three_pointer"),
                DB::raw("MAX(four_pointer) AS max_four_pointer"),
                DB::raw("MAX(penalty_cnt) AS max_penalty_cnt"),
                DB::raw("MAX(rebound_cnt) AS max_rebound_cnt"),
                DB::raw("MAX(assist_cnt) AS max_assist_cnt"),

                DB::raw("MIN(total_pointer) AS min_pointer"),
                DB::raw("MIN(two_pointer) AS min_two_pointer"),
                DB::raw("MIN(three_pointer) AS min_three_pointer"),
                DB::raw("MIN(four_pointer) AS min_four_pointer"),
                DB::raw("MIN(penalty_cnt) AS min_penalty_cnt"),
                DB::raw("MIN(rebound_cnt) AS min_rebound_cnt"),
                DB::raw("MIN(assist_cnt) AS min_assist_cnt"),
            ])
            ->where(['user_id' => auth()->id()])
            ->first();

        foreach ($list->toArray() as $k => $item) {
            $list->$k = floatval($item);
        }
        return $this->success($list);
    }

    //历史战绩
    public function history(Request $request)
    {
        $q = UserGrade::query();

        $list = $q
            ->from('user_grade as UG')
            ->leftJoin('match_team as MT', 'MT.id', '=', 'UG.rival_team_id')
            ->leftJoin('competition_match as CMT', 'CMT.id', '=', 'UG.competition_match_id')
            ->select([
                'UG.id',
                'CMT.c_date',
                'UG.total_pointer',
                'UG.rebound_cnt',
                'UG.assist_cnt',
                'MT.team_name',
            ])
            ->where(['user_id' => auth()->id()])
            ->orderByDesc('UG.created_at')
            ->paginate($request->get('pageSize'));

        return $this->successPage($list);
    }

    //排行榜类型
    public function RankingCategory(Request $request)
    {
        $q = Ranking::query();
        $q->orderByDesc('id');
        $q->select([
            'id',
            'ranking_name'
        ]);
        $list = $q
            ->where(['status' => 1])
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->get();

        return $this->success($list);
    }

    //排行榜
    public function RankingList(Request $request)
    {
        if (!$request->get('ranking_id')) {
            $ranking = Ranking::query()->orderByDesc('id')->first();
            $rankingId = $ranking->id;
        } else {
            $rankingId = $request->get('ranking_id');
        }

        $q = RankingPerson::query();

        $list = $q
            ->select([
                'id',
                'ranking_id',
                'person_name',
                'team_name',
                'avatar_path',
                'grade',
                'tag',
            ])
            ->where(['ranking_id' => $rankingId])
            ->orderByDesc('grade')
            ->get();

        return $this->success($list);
    }

    //排行榜
    public function RankingShow(Request $request)
    {
        if (!$request->get('ranking_person_id')) {
            return $this->error_400('排行榜人员ID必传');
        }
        $q = RankingPerson::query()->with(['RankingPersonGrade'])->where(['id' => $request->get('ranking_person_id')]);

        $list = $q
            ->select([
                'id',
                'ranking_id',
                'person_name',
                'team_name',
                'avatar_path',
                'cnt',
                'total_grade',
                'avg_grade',
                'grade',
                'age',
                'weight',
                'height',
                'tag',
            ])
            ->orderByDesc('grade')
            ->get();

        return $this->success($list);
    }

    //签约球员列表
    public function contractList(Request $request)
    {
        $cp = ContractPlayer::query()
            ->with(['grades'])
            ->orderByDesc('sort')
            ->orderBy('id');
        $list = $cp->get();

        return $this->success($list);
    }

}
