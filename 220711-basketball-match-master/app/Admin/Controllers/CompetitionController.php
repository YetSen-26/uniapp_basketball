<?php

namespace App\Admin\Controllers;

use App\Models\Admin\Competition;
use App\Models\Category;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class CompetitionController extends AdminController
{
    protected $title = '赛事列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make((new Competition())->with(['category']), function (Grid $grid) {
            $grid->model()->orderByDesc('id');

            $grid->column('id', 'ID');
            $grid->column('competition_name', '赛事名称');
            $grid->column('category.name', '赛事分类');
            $grid->column('entry_fee', '报名费');
            $grid->column('deposit_fee', '保证金');
            $grid->column('begin_date', '开始日期');
            $grid->column('end_date', '截止日期');
            $grid->column('expiration_date', '报名截止日期');
            $grid->column('venue_name', '场馆');
            $grid->column('venue_address', '场馆地址');
            $grid->column('person_limit', '报名人数上限');
//            $grid->column('created_at');
//            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('competition_name', '赛事名称')->width('25%');
                $filter->like('venue_name', '场馆名称')->width('25%');
                $filter->equal('expiration_date')->date()->width('25%');
            });

            $grid->actions(function (Grid\Displayers\Actions $action) {
                $action->append("<a href='competitionApply?competition_id=" . $action->row->id . "'>报名列表</a>");
                $action->append("<a href='matchCategory?competition_id=" . $action->row->id . "'>赛事分类</a>");
                $action->append("<a href='match?competition_id=" . $action->row->id . "'>比赛列表</a>");
            });

//            $grid->disableCreateButton();
//            $grid->disableDeleteButton();
//            $grid->disableToolbar();

//            $grid->disableEditButton();
            $grid->disableViewButton();
//            $grid->disablePagination();
//            $grid->enableDialogCreate();
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
        return Show::make($id, new Category(), function (Show $show) {
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
        return Form::make(new Competition(), function (Form $form) {
            $form->display('id');
            $category = Category::query()->where(['status' => 1])
                ->orderByDesc('sort')
                ->orderByDesc('id')
                ->get()
                ->pluck('name', 'id');
            $form->select('category_id', '赛事分类')->options($category);
            $form->text('competition_name', '赛事名称')->required();
            $form->currency('entry_fee', '报名费')->symbol('￥')->required();
            $form->currency('deposit_fee', '保证金')->symbol('￥');
            $form->date('begin_date', '开始日期');
            $form->date('end_date', '截止日期');
            $form->date('expiration_date', '报名截止日期');
            $form->text('venue_name', '场馆')->required();
            $form->text('venue_address', '场馆地址')->required();
            $form->text('person_limit', '报名人数上限')->required();
            $form->image('cover_path', '封面图')
                ->uniqueName()->autoUpload();

            $form->disableViewButton();
            $form->disableViewCheck();

            $form->saving(function (Form $form) {
//                if ($form->entry_fee < 0.01) {
//                    return $form->response()->error('报名费最低0.01元');
//                }
            });

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
