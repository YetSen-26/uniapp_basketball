<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\ActiveCompany;
use App\Admin\Actions\Grid\AllActive;
use App\Admin\Renderable\UserDetails;
use App\Models\Admin\AdminRoleUser;
use App\Models\Admin\AdminUser;
use App\User;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{
    protected $title = '用户';


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(User::query(), function (Grid $grid) {
            $grid->column('nickname', '名称');
            $grid->column('img_path', '头像')->image('', 50, 50);
            $grid->column('mobile', '手机号');
            $grid->column('', '详情')
                ->display('详情')
                ->expand(function () {
                    return UserDetails::make(['id' => $this->id]);
                });
            $grid->column('status', '状态')
                ->switch('', true);

            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->tools([
                new AllActive('全体激活', 1, 1),
                new AllActive('全体冻结', 0, 1),
            ]);

            $grid->batchActions([
                new ActiveCompany('批量激活', 1, 1),
                new ActiveCompany('批量冻结', 0, 1)
            ]);

            $grid->model()
                ->orderBy('id', 'desc');

            $grid->disableCreateButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->panel();
                $filter->like('mobile', '手机号')->width('25%');
                $filter->like('nickname', '名称')->width('25%');
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
        return Show::make($id, new User(), function (Show $show) {
            $show->field('id');
            $show->field('nickname', '昵称');
            $show->field('img_path', '头像')->image('', 50, 50);
            $show->field('mobile', '手机号');
            $show->field('openid', 'OPENID');
            $show->field('desc', '学校');
            $show->field('birthday', '生日');
            $show->field('weight', '体重');
            $show->field('height', '身高');
            $show->field('idcard', '身份证号');
            $show->field('address', '居住地址');
            $show->field('t_shirt_size', '上衣尺寸');
            $show->field('gold_cnt', '金币余额');
            $show->field('status', '状态')->using(['-1' => '冻结', '1' => '正常']);
            $show->field('created_at');
            $show->field('updated_at');

            $show->disableEditButton();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new User(), function (Form $form) {
            $form->display('id');
            $form->text('nickname', '名称')->required();
            $id = $form->getKey();
            if (!$id) {
                $form->mobile('mobile', '手机号')->required();
            } else {
                $form->display('mobile', '手机号');
            }
            $form->image('img_path', '头像')
                ->uniqueName()->autoUpload();
            $form->switch('status', '状态');
            $form->text('desc', '简介');
            $form->date('birthday', '生日');
            $form->text('weight', '体重');
            $form->text('height', '身高');
            $form->number('gold_cnt', '金币余额');
            $form->display('created_at');
            $form->display('updated_at');

        });
    }
}
