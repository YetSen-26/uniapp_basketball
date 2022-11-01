<?php

namespace App\Admin\Controllers;

use App\Models\Ranking;
use App\Models\Admin\RankingPerson;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class RankingListController extends AdminController
{
    protected $title = '排行榜列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make((new RankingPerson())->with(['ranking']), function (Grid $grid) {
            $ranking = Ranking::query()->where(['status' => 1])
                ->orderByDesc('sort')
                ->orderByDesc('id')
                ->get()
                ->pluck('ranking_name', 'id');

            $grid->model()->orderByDesc('grade')->orderByDesc('id');
            $grid->column('person_name', '名称')->editable(true);
            $grid->column('avatar_path', '头像')->image('', 50, 50);
            $grid->column('ranking_id', '所属排行榜')->select($ranking);
            $grid->column('team_name', '所属球队')->editable(true);
            $grid->column('grade', '成绩')->editable(true);
            $grid->column('tag', '标签')->editable(true);

            $grid->filter(function (Grid\Filter $filter)use($ranking) {
                $filter->expand();
                $filter->panel();

                $filter->like('person_name', '名称')->width('25%');
                $filter->like('team_name', '所属球队')->width('25%');
                $filter->equal('ranking_id', '排行榜')->select($ranking)->width('25%');
            });

//            $grid->quickCreate(function (Grid\Tools\QuickCreate $create)use($ranking) {
//                $create->text('person_name', '名称');
//                $create->select('ranking_id', '所属排行榜')->options($ranking);
//                $create->text('team_name', '所属球队');
//                $create->text('grade', '成绩');
//                $create->text('tag', '标签');
//            });
            $grid->showFilter();

//            $grid->disableEditButton();
//            $grid->disableCreateButton();
            $grid->disableViewButton();
//            $grid->disablePagination();
            $grid->enableDialogCreate();
            $grid->toolsWithOutline(false);

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
        return Show::make($id, new RankingPerson(), function (Show $show) {
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
        return Form::make((new RankingPerson())->with(['rpg']), function (Form $form) {
            $ranking = Ranking::query()->where(['status' => 1])
                ->orderByDesc('sort')
                ->orderByDesc('id')
                ->get()
                ->pluck('ranking_name', 'id');

            $form->display('id');
            $form->text('person_name', '名称')->required();
            $form->image('avatar_path', '头像')
                ->uniqueName()->autoUpload();
            $form->select('ranking_id', '所属排行榜')->options($ranking);
            $form->text('team_name', '所属球队');
            $form->text('age', '年龄');
            $form->text('height', '身高');
            $form->text('weight', '体重');
            $form->text('grade', '成绩');
            $form->text('avg_grade', '场均成绩');
            $form->text('total_grade', '总成绩');
            $form->text('cnt', '场次');
            $form->hasMany('rpg','战绩', function (Form\NestedForm $form) {
                $form->date('c_date', '日期');
                $form->text('rel_team_name', '对手球队');
                $form->text('grade', '得分');
                $form->text('rebound_cnt', '篮板数');
                $form->text('assist_cnt', '助攻数');
//                $form->text('site', '站点');
//                $form->number('sort', '排序值');
            })->useTable();
            $form->text('tag', '标签');
            $form->display('created_at');
            $form->display('updated_at');
            $form->disableViewButton();
            $form->disableViewCheck();
        });
    }
}
