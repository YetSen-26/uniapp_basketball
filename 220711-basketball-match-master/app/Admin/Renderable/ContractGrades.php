<?php

namespace App\Admin\Renderable;

use App\Models\ContractPlayerGrade;
use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Table;

class ContractGrades extends LazyRenderable
{
    public function render()
    {
        $data = [];
        $list = ContractPlayerGrade::query()->where(['ct_player_id' => $this->key])->get();
        foreach ($list as $item) {
            $data[] = [
                $item->c_date,
                $item->site,
                $item->grade,
                $item->sort,
            ];
        }
        return Table::make(['日期', '站点', '成绩', '排序值'], $data);
    }
}
