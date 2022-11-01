<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Ranking;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class CategoryController extends AdminController
{
    protected $title = '赛事分类';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Category(), function (Grid $grid) {
//            $grid->column('name','名称');
//            $grid->column('icon','图标')->image('',35,35);
            $grid->model()->orderByDesc('sort')->orderByDesc('id');
            $grid->column('name','名称')->editable(true);
            $grid->column('status','状态')->switch('', true);
            $grid->column('sort','排序值')->editable(true);
//            $grid->column('created_at');
//            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('name')->width('25%');
            });

//            $grid->disableCreateButton();
//            $grid->disableDeleteButton();
            $grid->disableToolbar();
            $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                $create->text('name', '名称');
                $create->text('sort', '排序值');
            });

            $grid->disableEditButton();
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
        return Show::make($id, new Category(), function (Show $show) {
            $show->field('id');
            $show->field('name','名称');
            $show->field('icon','图标')->image('',50,50);
            $show->field('sort','排序值');
            $show->field('status','状态')->using(['0' => '关闭', '1' => '开启']);
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
        return Form::make(new Category(), function (Form $form) {
            $form->display('id');
            $form->text('name','名称');
            $form->number('sort','排序');
            $form->switch('status','状态')
                ->customFormat(function ($v) {
                    return $v;
                })
                ->saving(function ($v) {
                    return $v;
                })
                ->default(1);

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
