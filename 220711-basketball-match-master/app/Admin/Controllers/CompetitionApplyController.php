<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\ContractGrades;
use App\Admin\Renderable\UserDetails;
use App\Models\Admin\Competition;
use App\Models\Category;
use App\Models\CompetitionApply;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class CompetitionApplyController extends AdminController
{
    protected $title = '赛事报名列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $q = (new CompetitionApply())->with(['user', 'competition'])->where(['status' => 1]);
        return Grid::make($q, function (Grid $grid) {
            $grid->model()->orderByDesc('id');
            $grid->column('id', 'ID');
            $grid->column('competition.competition_name', '赛事名称');
            $grid->column('user.nickname', '姓名');
            $grid->column('user.img_path', '头像')->image('', 50, 50);;
            $grid->column('user.mobile', '手机号');
            $grid->column('', '详情')
                ->display('详情')
                ->expand(function () {
                    return UserDetails::make(['id' => $this->user_id]);
                });
            $grid->column('entry_fee', '报名费');
            $grid->column('deposit_fee', '保证金');
            $grid->column('is_pass', '审核状态')
                ->switch('', true);
            $grid->column('created_at');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->panel();
                $filter->equal('competition_id', '赛事')->select(Competition::query()->get()->pluck('competition_name', 'id'))->width('25%');
                $filter->like('venue_name', '场馆名称')->width('25%');
                $filter->like('user.nickname', '姓名')->width('25%');
                $filter->like('user.mobile', '手机号')->width('25%');
            });

            $grid->actions(function (Grid\Displayers\Actions $action) {
                $action->append("<a href='competitionApply?competition_id=" . $action->row->id . "'>报名列表</a>");
            });

            $grid->disableCreateButton();
            $grid->disableDeleteButton();
            $grid->disableToolbar();
            $grid->disableActions();
            $grid->disableEditButton();
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
        return Form::make(new CompetitionApply(), function (Form $form) {
//            $form->display('id');
//            $category = Category::query()->where(['status' => 1])
//                ->orderByDesc('sort')
//                ->orderByDesc('id')
//                ->get()
//                ->pluck('name', 'id');
//            $form->select('category_id', '赛事分类')->options($category);
//            $form->text('competition_name', '赛事名称')->required();
//            $form->currency('entry_fee', '报名费')->symbol('￥')->required();
//            $form->currency('deposit_fee', '保证金')->symbol('￥');
//            $form->date('begin_date', '开始日期');
//            $form->date('end_date', '截止日期');
//            $form->date('expiration_date', '报名截止日期');
//            $form->text('venue_name', '场馆')->required();
            $form->text('is_pass');
//            $form->image('cover_path', '封面图')
//                ->uniqueName()->autoUpload();

            $form->disableViewButton();
            $form->disableViewCheck();

//            $form->saving(function (Form $form) {
//                if ($form->entry_fee < 0.01) {
//                    return $form->response()->error('报名费最低0.01元');
//                }
//            });

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
