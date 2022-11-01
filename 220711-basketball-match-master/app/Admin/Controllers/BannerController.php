<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Banner;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class BannerController extends AdminController
{
    protected $title = '轮播图';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Banner(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('img_path', '图标')->image('', 50, 50);
            $grid->column('sort', '排序')->editable(true)->sortable();
//            $grid->column('type');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->model()->orderBy('id', 'desc');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('type')->radio([''=>'全部','1' => '首页', '2' => '社区'])->default('');

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
        return Show::make($id, new Banner(), function (Show $show) {
            $show->field('id');
            $show->field('img_path', '图标')->image('', 50, 50);
//            $show->field('url', '跳转地址');
            $show->field('content','内容')->view('admin.content');

            $show->field('sort', '排序');
            $show->field('type')->using(['1' => '首页', '2' => '社区']);
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
        return Form::make(new Banner(), function (Form $form) {
            $form->display('id');
//            $form->text('url');
            $form->image('img_path', '图标')
                ->uniqueName()->autoUpload();
            $form->number('sort', '排序');
//            $form->radio('type', '类型')->options(['1' => '首页', '2' => '社区'])->default('1');
            $form->editor('content','内容');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
