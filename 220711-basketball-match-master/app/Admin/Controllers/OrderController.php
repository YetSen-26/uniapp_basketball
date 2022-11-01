<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Order;
use App\Models\GoodsOrder;
use App\Models\OrderDetailView;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class OrderController extends AdminController
{
    protected $title = '订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(OrderDetailView::query(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('order_no', '订单编号');
            $grid->column('goods_name', '订单商品');
            $grid->column('nickname', '用户名称');
            $grid->column('mobile', '用户电话');
            $grid->column('type', '订单类型')->using(['cash' => '现金订单', 'gold' => '金币订单']);
            $grid->column('price', '金额');
            $grid->column('num', '数量');
            $grid->column('total_amount', '总价');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->panel();

                $filter->like('order_no', '订单编号')->width('25%');
                $filter->like('goods_name', '订单商品')->width('25%');
                $filter->like('nickname', '用户名称')->width('25%');
                $filter->like('mobile', '用户电话')->width('25%');
                $filter->equal('type', '订单类型')->select(['cash' => '现金订单', 'gold' => '金币订单'])->width('25%');
                $filter->between('created_at', '日期')->date()->width('25%');
            });

//            $grid->model()->where('status', '=', 1);
//            $grid->model()->orderBy('id', 'desc');
            $grid->disableActions(true);
            $grid->disableViewButton(true);
            $grid->disableEditButton(true);
            $grid->disableDeleteButton(true);
            $grid->disableCreateButton(true);
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
        return Show::make($id, Order::with(['user']), function (Show $show) {
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
        return Form::make(new Order(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('post_id');
            $form->text('amount');
            $form->text('person_num');
            $form->text('look_num');
            $form->text('look_amount');
            $form->text('look_residue_amount');
            $form->text('look_residue_num');
            $form->text('share_num');
            $form->text('share_amount');
            $form->text('share_residue_amount');
            $form->text('share_residue_num');
            $form->text('share_rate');
            $form->text('plat_rate');
            $form->text('plat_amount');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
