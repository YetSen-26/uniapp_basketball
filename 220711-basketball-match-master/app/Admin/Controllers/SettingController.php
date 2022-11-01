<?php

namespace App\Admin\Controllers;

use App\Models\System\SystemDict;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SettingController extends AdminController
{
    protected $title = '系统设置';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SystemDict(), function (Grid $grid) {
//            $grid->column('id')->sortable();
            $grid->column('desc', '描述');
            $grid->column('key', '系统标识');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('desc','描述');
            });

            $grid->disableCreateButton();
            $grid->disableDeleteButton();
            $grid->disableToolbar();
            $grid->disablePagination();

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
        return Show::make($id, new SystemDict(), function (Show $show) {
            $show->field('id');
            $show->field('desc', '描述');
            if ($show->model()->key == SystemDict::SYS_SIGNIN_GOLD) {
                $show->field('value', '内容');
            } else if ($show->model()->key == SystemDict::SYS_GUESSING_WIN_GOLD_RATE) {
                $show->field('value', '内容');
            } else {
                $show->field('value', '内容')->view('admin.setting');
//                $show->value->view('admin.setting');
            }
            $show->field('updated_at');

            $show->disableDeleteButton();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SystemDict(), function (Form $form) {
            $id = $form->getKey();
            $form->display('id');
            $form->text('desc', '描述')->disable();
            if ($form->model()->key == SystemDict::SYS_SIGNIN_GOLD) {
                $form->number('value', '金币数');
            } else if ($form->model()->key == SystemDict::SYS_GUESSING_WIN_GOLD_RATE) {
                $form->rate('value', '竞猜金币获胜比例');
            } else {
                $form->editor('value', '内容');
            }

            $form->display('updated_at');

            $form->disableDeleteButton();

//            $form->submitted(function (Form $form) {
//                // 获取用户提交参数
//                $value = $form->value;
//                $id = $form->getKey();
//                $setting = SystemDict::find($id);
//                $errorMsg = '';
//                if ($errorMsg) {
//                    // 中断后续逻辑
//                    return $form->response()->error($errorMsg);
//                }
//            });
        });
    }
}
