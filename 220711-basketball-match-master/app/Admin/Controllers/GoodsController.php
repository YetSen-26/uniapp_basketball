<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Order;
use App\Models\Goods;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class GoodsController extends AdminController
{
    protected $title = '商品管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Goods::query(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('goods_name', '商品名称');
            $grid->column('cover_path', '封面图')->image('', 50, 50);
            $grid->column('price', '单价')->display(function($v){
                return $this->type == 'cash'?$this->price:$this->gold_price;
            });
            $grid->column('type', '类型')->using(['cash' => '现金商品', 'gold' => '金币商品']);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('goods_name', '商品名称')->width('25%');
                $filter->equal('type', '商品类型')->select(['cash' => '现金商品', 'gold' => '金币商品'])->width('25%');
            });

            $grid->model()->orderBy('id', 'desc');
            $grid->disableViewButton(true);
//            $grid->disableEditButton(true);
//            $grid->disableDeleteButton(true);
//            $grid->disableCreateButton(true);
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
        return Show::make($id, Goods::with(['user']), function (Show $show) {
            $show->field('id');
            $show->field('order_no', '需求标题');
            $show->field('user.mobile', '手机号');
            $show->field('amount', '金额');
            $show->field('pay_from', '支付方式')->using(['alipay' => '支付宝', 'wechat' => '微信']);
            $show->field('agent_level_name', '代理名称');
            $show->field('month', '会员月数')->as(function ($month) {
                if ($month == '-1') {
                    return '永久会员';
                }
                return $month;
            });
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
        return Form::make(new Goods(), function (Form $form) {
            $form->display('id');
            $form->text('goods_name', '商品名称');
            $form->image('cover_path', '封面图')
                ->uniqueName()->autoUpload();
            $form->radio('type', '商品类型')
                ->options(['cash' => '现金商品', 'gold' => '金币商品'])
                ->when('cash', function (Form $form) {
                    $form->currency('price', '价格')->symbol('￥');
                })->when('gold', function (Form $form) {
                    $form->number('gold_price', '价格');
                })->default('cash');

            $form->number('sort','排序值');
            $form->editor('content', '内容');;

            $form->display('created_at');
            $form->display('updated_at');
            $form->disableViewButton();
            $form->disableViewCheck();
            $form->saving(function (Form $form) {
                if ($form->type == 'gold') {
                    $form->price = $form->gold_price;
                }
            });
        });
    }
}
