<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class GoodsOrder extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'goods_orders';
    protected $guarded = [];

    public function goods(){
        return $this->belongsTo(Goods::class,'goods_id','id');
    }
}
