<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Suggest;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SuggestController extends AdminController
{
    protected $title = '反馈建议';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Suggest::with(['user']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('content', '内容');
            $grid->column('user.mobile', '用户电话');
            $grid->column('created_at');

            $grid->model()->orderBy('id', 'desc');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand();
                $filter->like('content', '内容')->width('25%');
                $filter->like('user.mobile', '手机号')->width('25%');
            });

            $grid->disableEditButton();
            $grid->disableCreateButton();
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
        return Show::make($id, Suggest::with(['user']), function (Show $show) {
            $show->field('id');
            $show->field('content', '内容');
            $show->field('user.mobile', '用户电话');
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
        return Form::make(new Suggest(), function (Form $form) {
            $form->display('id');
            $form->text('content');
            $form->text('user_id');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
