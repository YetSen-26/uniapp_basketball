<?php


namespace App\Admin\Renderable;


use App\Models\CompetitionApply;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserTeamTable extends LazyRenderable
{
    public function grid(): Grid
    {
        // 获取外部传递的参数
        $competitionID = Cache::get($this->uniqid_id . '_competition_id') ? Cache::get($this->uniqid_id . '_competition_id') : '';
        $type = $this->type;
        $q = CompetitionApply::query()
            ->from('competition_apply', 'TCA')
            ->join('users AS TU', 'TU.id', '=', 'TCA.user_id')
            ->select([
                'TU.id',
                'TU.nickname',
                'TU.birthday',
                'TU.weight',
                'TU.height',
                'TU.desc',
                'TU.school',
                'TU.mobile',
            ])->where(['TCA.status' => 1,'is_pass'=>1]);

        if (!empty($competitionID)) {
            $q->where(['TCA.competition_id' => $competitionID]);
        } else {
            $q->where(['TCA.competition_id' => '']);
        }
        if ($type == 'a' && !empty(Cache::get($this->uniqid_id . '_team_b_ids'))) {
            $q->whereNotIn('TU.id', explode(',', Cache::get($this->uniqid_id . '_team_b_ids')));
        }

        if ($type == 'b' && !empty(Cache::get($this->uniqid_id . '_team_a_ids'))) {
            $q->whereNotIn('TU.id', explode(',', Cache::get($this->uniqid_id . '_team_a_ids')));
        }

        return Grid::make($q, function (Grid $grid) {
            $grid->column('id', 'id');
            $grid->column('nickname', '昵称');
            $grid->column('birthday', '生日');
            $grid->column('weight', '体重');
            $grid->column('height', '身高');
            $grid->column('school', '学校');
            $grid->column('desc', '简介');
            $grid->column('mobile', '手机号');

            $grid->paginate(10);
            $grid->disableActions();
            $grid->disableBatchDelete();
            $grid->disableFilter();
            $grid->disableFilterButton();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('nickname', '昵称')->width(4);
                $filter->like('mobile', '手机号')->width(4);
            });
        });
    }

}
