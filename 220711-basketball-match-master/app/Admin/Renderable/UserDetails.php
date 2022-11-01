<?php

namespace App\Admin\Renderable;

use App\Models\ContractPlayerGrade;
use App\User;
use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Table;

class UserDetails extends LazyRenderable
{
    public function render()
    {
        $data = [];
        $item = User::query()->where(['id' => $this->id])->first();
        $data[] = [
            $item->idcard,
            $item->school,
            $item->gold_cnt,
            $item->weight,
            $item->height,
            $item->desc,
            $item->t_shirt_size,
            $item->openid,
        ];
        return Table::make(['身份证号', '学校', '金币余额', '体重', '身高', '简介', '上衣尺寸', 'OPENID'], $data);
    }
}
