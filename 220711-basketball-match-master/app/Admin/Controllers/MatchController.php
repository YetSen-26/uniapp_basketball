<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\SettleMatch;
use App\Admin\Renderable\UserTeamTable;
use App\Models\Admin\Competition;
use App\Models\Admin\CompetitionMatch;
use App\Models\Admin\ContractPlayer;
use App\Models\Admin\MatchTeam;
use App\Models\Admin\ViewMatchTeam;
use App\Models\MatchCategory;
use App\Models\MatchTeamUser;
use App\Models\UserGrade;
use App\User;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Log;

class MatchController extends AdminController
{
    protected $title = '比赛列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make((new ViewMatchTeam()), function (Grid $grid) {
            $grid->model()->orderByDesc('competition_id')->orderByDesc('id');
            $grid->column('competition_name', '赛事名称');
            $grid->column('match_category_name', '比赛分类');
            $grid->column('a_team_name', 'A队 队名');
            $grid->column('a_logo_path', 'A队 队标')->image('', 50, 50);
            $grid->column('a_grade', 'A队 成绩');
            $grid->column('b_team_name', 'B队 队名');
            $grid->column('b_logo_path', 'B队 队标 ')->image('', 50, 50);
            $grid->column('b_grade', 'B队 成绩');
            $grid->column('c_date', '比赛日期');
            $grid->column('match_date_begin', '比赛开始日期');
            $grid->column('match_date_end', '比赛结束日期');
            $grid->column('is_settle', '结算状态')->using([
                '0' => '未结算',
                '1' => '已结算'
            ]);

            $grid->column('status', '比赛状态')->options()->radio([
                '0' => '未开始',
                '1' => '进行中',
                '2' => '已结束',
            ], true);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->panel();
                $filter->equal('competition_id', '赛事')
                    ->select(
                        Competition::query()->get()->pluck('competition_name', 'id')
                    )->width('25%')->load('match_category_id', 'api/matchCategory');
                $mt = MatchCategory::query()
                    ->where(['competition_id' => request('competition_id') ?? 0])
                    ->get()->pluck('name', 'id');
                $filter->equal('match_category_id', '比赛类型')
                    ->select($mt)
                    ->width('25%');
                $filter->where('team_name', function ($query) {
                    $query->where('a_team_name', 'like', "%{$this->input}%")
                        ->orWhere('b_team_name', 'like', "%{$this->input}%");
                }, '队名')->width('25%');
                $filter->between('c_date', '比赛日期')->date()->width('25%');
                $filter->equal('status', '比赛状态')->select([
                    '0' => '未开始',
                    '1' => '进行中',
                    '2' => '已结束',
                ])->width('25%');
                $filter->equal('is_settle', '结算状态')->select([
                    '0' => '未结算',
                    '1' => '已结算'
                ])->width('25%');
            });

            $grid->actions(function (Grid\Displayers\Actions $action) {
                if ($action->row->status == 2) {
                    $action->append("<a href='matchUser?competition_match_id=" . $action->row->id . "'>比赛结果</a>");
                    if (empty($action->row->is_settle)) {
                        $action->append(new SettleMatch());
                    }
                }

                if ($action->row->status > 0) {
                    $action->disableEdit();
                }
            });

            $grid->showFilter();
//            $grid->disableToolbar();
//            $grid->disableEditButton();
//            $grid->disableCreateButton();
            $grid->disableViewButton();
//            $grid->enableDialogCreate();
//            $grid->toolsWithOutline(false);

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
        return Form::make((new CompetitionMatch())->with(['matchCategory']), function (Form $form) {
            $form->display('id');
            $unionId = md5(time());
            $form->hidden('status')->default(0);
            $form->hidden('union_id')->value($unionId);
            $com = Competition::query()->get()->pluck('competition_name', 'id');
            $form->select('competition_id', '赛事')
                ->options($com)
                ->required()
                ->addElementClass('quick_competition_select')
                ->load('match_category_id', 'api/matchCategory');

            $form->select('match_category_id', '比赛分类')->required();

            $form->divider('球队A');

            $form->date('c_date', '比赛日期');
            $form->datetime('match_date_begin', '开始时间');
            $form->datetime('match_date_end', '结束时间');
            $form->text('team_a_name_x', '球队A 名称')->required();
            $form->image('team_a_logo_x_path', '球队A logo')
                ->uniqueName()->autoUpload();

            $form->multipleSelectTable('usera_list', '球队A 队员')
                ->title('球队A 队员')
                ->from(UserTeamTable::make(['uniqid_id' => $unionId, 'type' => 'a']))
                ->options(function ($v) {
                    if (count($v) == 0) {
                        return [];
                    }
                    $m = User::query()->whereIn('id', $v)->get();
                    if (!$v || !$m) {
                        return [];
                    }
                    $data = [];
                    foreach ($m as $item) {
                        $data[$item->id] = $item->nickname;
                    }
                    return $data;
                })
                ->addElementClass('quick_select_team_a_ids')
                ->pluck('nickname', 'id');

            $form->divider('球队B');
            $form->text('team_b_name_x', '球队B名称')->required();
            $form->image('team_b_logo_x_path', '球队B Logo')
                ->uniqueName()->autoUpload();
            $form->multipleSelectTable('userb_list', '球队B 队员')
                ->title('球队B 队员')
                ->from(UserTeamTable::make(['uniqid_id' => $unionId, 'type' => 'b']))
                ->options(function ($v) {
                    if (count($v) == 0) {
                        return [];
                    }
                    $m = User::query()->whereIn('id', $v)->get();
                    if (!$v || !$m) {
                        return [];
                    }
                    $data = [];
                    foreach ($m as $item) {
                        $data[$item->id] = $item->nickname;
                    }
                    return $data;
                })
                ->addElementClass('quick_select_team_b_ids')
                ->pluck('nickname', 'id');

            $form->disableViewButton();
            $form->disableViewCheck();

            $form->ignore(['union_id']);

            $form->saving(function (Form $form) {
                if (count(explode(',', $form->usera_list)) != count(explode(',', $form->userb_list))) {
                    return $form->response()->error('两队队员人数不一致');
                }

                if (count(explode(',', $form->usera_list)) == 0 || count(explode(',', $form->userb_list)) == 0) {
                    return $form->response()->error('请选择队员');
                }
            });

            \Admin::script($this->script());

            $form->saved(function (Form $form) {
                $m = $form->repository()->model();
                if ($form->isCreating()) {
                    $mtA = new MatchTeam();
                    $mtA->fill([
                        'match_id' => $m->id,
                        'type' => 'a',
                        'team_name' => $m->team_a_name_x,
                        'logo_path' => $m->team_a_logo_x_path,
                        'grade' => 0,
                    ]);
                    $mtA->save();
                    $m->team_a_id = $mtA->id;
                    foreach ($m->usera_list as $item) {
                        $mtuA = new MatchTeamUser();
                        $mtuA->fill([
                            'match_id' => $m->id,
                            'user_id' => $item,
                            'team_id' => $mtA->id
                        ]);
                        $mtuA->save();

                        $ug = new UserGrade();
                        $ug->fill([
                            'competition_id' => $m->competition_id,
                            'competition_match_id' => $m->id,
                            'owner_team_id' => $mtA->id,
                            'rival_team_id' => 0,
                            'user_id' => $item,
                        ]);
                        $ug->save();
                    }

                    $mtB = new MatchTeam();
                    $mtB->fill([
                        'match_id' => $m->id,
                        'type' => 'b',
                        'team_name' => $m->team_b_name_x,
                        'logo_path' => $m->team_b_logo_x_path,
                        'grade' => 0,
                    ]);
                    $mtB->save();
                    $m->team_b_id = $mtB->id;
                    foreach ($m->userb_list as $item) {
                        $mtuB = new MatchTeamUser();
                        $mtuB->fill([
                            'match_id' => $m->id,
                            'user_id' => $item,
                            'team_id' => $mtB->id
                        ]);
                        $mtuB->save();

                        $ug = new UserGrade();
                        $ug->fill([
                            'competition_id' => $m->competition_id,
                            'competition_match_id' => $m->id,
                            'owner_team_id' => $mtB->id,
                            'rival_team_id' => 0,
                            'user_id' => $item,
                        ]);
                        $ug->save();
                    }
                    $m->save();

                    UserGrade::query()
                        ->where([
                            'competition_match_id' => $m->id,
                            'owner_team_id' => $mtA->id,
                        ])
                        ->update([
                            'rival_team_id' => $mtB->id
                        ]);

                    UserGrade::query()
                        ->where([
                            'competition_match_id' => $m->id,
                            'owner_team_id' => $mtB->id,
                        ])
                        ->update([
                            'rival_team_id' => $mtA->id
                        ]);
                }

                if ($form->isEditing() && $form->usera_list && $form->userb_list) {
                    $mtA = MatchTeam::query()->where(['match_id' => $m->id, 'type' => 'a'])->first();
                    $mtA->fill([
                        'team_name' => $m->team_a_name_x,
                        'logo_path' => $m->team_a_logo_x_path,
                    ]);
                    $mtA->save();
                    UserGrade::query()->where([
                        'competition_match_id' => $m->id,
                    ])->delete();
                    MatchTeamUser::query()->where([
                        'match_id' => $m->id,
                        'team_id' => $mtA->id,
                    ])->delete();
                    foreach ($m->usera_list as $item) {
                        $mtuA = new MatchTeamUser();
                        $mtuA->fill([
                            'match_id' => $m->id,
                            'user_id' => $item,
                            'team_id' => $mtA->id
                        ]);
                        $mtuA->save();

                        $ug = new UserGrade();
                        $ug->fill([
                            'competition_id' => $m->competition_id,
                            'competition_match_id' => $m->id,
                            'owner_team_id' => $mtA->id,
                            'rival_team_id' => 0,
                            'user_id' => $item,
                        ]);
                        $ug->save();
                    }

                    $mtB = MatchTeam::query()->where(['match_id' => $m->id, 'type' => 'b'])->first();
                    $mtB->fill([
                        'team_name' => $m->team_b_name_x,
                        'logo_path' => $m->team_b_logo_x_path,
                    ]);
                    $mtB->save();
                    MatchTeamUser::query()->where([
                        'match_id' => $m->id,
                        'team_id' => $mtB->id,
                    ])->delete();
                    foreach ($m->userb_list as $item) {
                        $mtuB = new MatchTeamUser();
                        $mtuB->fill([
                            'match_id' => $m->id,
                            'user_id' => $item,
                            'team_id' => $mtB->id
                        ]);
                        $mtuB->save();

                        $ug = new UserGrade();
                        $ug->fill([
                            'competition_id' => $m->competition_id,
                            'competition_match_id' => $m->id,
                            'owner_team_id' => $mtB->id,
                            'rival_team_id' => 0,
                            'user_id' => $item,
                        ]);
                        $ug->save();
                    }

                    UserGrade::query()
                        ->where([
                            'competition_match_id' => $m->id,
                            'owner_team_id' => $mtA->id,
                        ])
                        ->update([
                            'rival_team_id' => $mtB->id
                        ]);

                    UserGrade::query()
                        ->where([
                            'competition_match_id' => $m->id,
                            'owner_team_id' => $mtB->id,
                        ])
                        ->update([
                            'rival_team_id' => $mtA->id
                        ]);
                }

            });
        });
    }


    public function script()
    {
        return <<<JS
            var unionId = $("input[name='union_id']").val();
            $('.quick_competition_select').on('change', function () {
                $.ajax({
                    url: '/admin/api/cacheMatchCreateInfo?union_id=' + unionId+'&competition_id='+this.value,
                    // beforeSend: function () {
                    //     Dcat.loading();
                    // },
                    // complete: function () {
                    //     Dcat.loading(false);
                    // }
                });

            });
            $("input[name='usera_list']").on('change', function () {
                $.ajax({
                    url: '/admin/api/cacheMatchCreateInfo?union_id=' + unionId+'&team_a_ids='+this.value,
                    // beforeSend: function () {
                    //     Dcat.loading();
                    // },
                    // complete: function () {
                    //     Dcat.loading(false);
                    // }
                });

            });
            $("input[name='userb_list']").on('change', function () {
                $.ajax({
                    url: '/admin/api/cacheMatchCreateInfo?union_id=' + unionId+'&team_b_ids='+this.value,
                    // beforeSend: function () {
                    //     Dcat.loading();
                    // },
                    // complete: function () {
                    //     Dcat.loading(false);
                    // }
                });

            });
            Dcat.triggerReady();
JS;
    }
}
