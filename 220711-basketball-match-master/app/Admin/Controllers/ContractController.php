<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\ContractGrades;
use App\Models\Admin\ContractPlayer;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ContractController extends AdminController
{
    protected $title = '签约球员列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make((new ContractPlayer())->with(['grades']), function (Grid $grid) {
            $grid->model()->orderByDesc('sort')->orderByDesc('id');
            $grid->column('name', '名称');
            $grid->column('avatar_path', '头像')->image('', 50, 50);
            $grid->column('cover_path', '封面')->image('', 50, 50);
            $grid->column('age', '年龄');
            $grid->column('grades', '战绩')
                ->display('战绩')
                ->expand(ContractGrades::make());
            $grid->column('weight', '体重');
            $grid->column('height', '身高');
            $grid->column('desc', '简介');
            $grid->column('sort', '排序值')->editable(true);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->panel();
                $filter->like('name', '名称')->width('25%');
            });

            $grid->showFilter();

//            $grid->disableEditButton();
//            $grid->disableCreateButton();
            $grid->disableViewButton();
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
        return Form::make(new ContractPlayer(), function (Form $form) {
            $form->display('id');
            $form->text('name', '名称')->required();
            $form->image('avatar_path', '头像')
                ->uniqueName()->autoUpload();
            $form->image('cover_path', '封面')
                ->uniqueName()->autoUpload();
            $form->text('age', '年龄');
            $form->text('weight', '体重');
            $form->text('height', '身高');
            $form->textarea('desc', '简介');
            $form->number('sort', '排序值');
            $form->disableViewButton();
            $form->disableViewCheck();
            $form->hasMany('grades','战绩', function (Form\NestedForm $form) {
                $form->date('c_date', '日期');
                $form->text('site', '站点');
                $form->text('grade', '得分');
                $form->number('sort', '排序值');
            })->useTable();


//            $form->display('created_at');
//            $form->display('updated_at');
        });
    }
}
