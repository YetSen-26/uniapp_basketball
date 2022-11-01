<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\UserTeamTable;
use App\Models\Admin\Competition;
use App\Models\Admin\CompetitionMatch;
use App\Models\Admin\ContractPlayer;
use App\Models\Admin\MatchTeam;
use App\Models\Admin\MatchTeamA;
use App\Models\Admin\MatchTeamB;
use App\Models\Admin\ViewMatchTeam;
use App\Models\MatchTeamUser;
use App\Models\UserGrade;
use App\User;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MatchUserController extends AdminController
{
    protected $title = '比赛结果';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $m = UserGrade::query()->with(['user', 'ownerTeam'])
            ->where([
                'competition_match_id' => request('competition_match_id'),
            ]);
        return Grid::make($m, function (Grid $grid) {
//            $grid->model()->orderByDesc('competition_id')->orderByDesc('id');
            $grid->column('user.nickname', '用户昵称');
            $grid->column('ownerTeam.team_name', '队伍名称');
            $grid->column('ownerTeam.logo_path', '队伍Logo')->image('', 50, 50);
            $grid->column('total_pointer', '总分')->editable(true);
            $grid->column('two_pointer', '2分球数')->editable(true);
            $grid->column('three_pointer', '3分球数')->editable(true);
            $grid->column('four_pointer', '4分球数')->editable(true);
            $grid->column('penalty_cnt', '罚球数')->editable(true);
            $grid->column('rebound_cnt', '篮板球数')->editable(true);
            $grid->column('assist_cnt', '助攻数')->editable(true);

            $grid->showFilter();
            $grid->disableToolbar();
            $grid->disableEditButton();
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disablePagination();
            $grid->disablePerPages();
            $grid->enableDialogCreate();
            $grid->toolsWithOutline(false);
            $grid->disableActions();
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new ContractPlayer(), function (Show $show) {
            $show->field('id');
            $show->field('name', '名称');
            $show->field('icon', '图标')->image('', 50, 50);
            $show->field('sort', '排序值');
            $show->field('status', '状态')->using(['0' => '关闭', '1' => '开启']);
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make((new UserGrade()), function (Form $form) {
            $form->display('id');
//            $form->hidden('team_a_id');
//            $form->hidden('team_b_id');
//            $form->hidden('match_id');

            $form->text('total_pointer');
            $form->text('two_pointer');
            $form->text('three_pointer');
            $form->text('four_pointer');
            $form->text('penalty_cnt');
            $form->text('rebound_cnt');
            $form->text('assist_cnt');

            $form->ignore(['team_id','match_id']);

            $form->saved(function (Form $form) {
                $m = $form->model();
                $mt = CompetitionMatch::query()->where(['id'=>$m->competition_match_id])->first();

                $ugs = UserGrade::query()
                    ->where([
                        'owner_team_id' => $mt->team_a_id,
                        'competition_match_id' => $mt->id
                    ])
                    ->get();
                $totalA = 0;
                foreach ($ugs as $item) {
                    $totalA += $item->total_pointer;
                }

                $ugs = UserGrade::query()
                    ->where([
                        'owner_team_id' => $mt->team_b_id,
                        'competition_match_id' => $mt->id
                    ])
                    ->get();
                $totalB = 0;
                foreach ($ugs as $item) {
                    $totalB += $item->total_pointer;
                }

                $ma = MatchTeam::query()->find($mt->team_a_id);
                $ma->grade = $totalA;
                $ma->save();

                $mb = MatchTeam::query()->find($mt->team_b_id);
                $mb->grade = $totalB;
                $mb->save();
            });
        });
    }
}
