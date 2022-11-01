<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\SystemMsg;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SystemMsgController extends AdminController
{
    protected $title = '系统消息';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SystemMsg(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('title');
//            $grid->column('content');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
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
        return Show::make($id, new SystemMsg(), function (Show $show) {
            $show->field('id');
            $show->field('title');
            $show->field('content');
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
        return Form::make(new SystemMsg(), function (Form $form) {
            $form->display('id');
            $form->text('title');
            $form->editor('content');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
