<?php


namespace App\Models\System;


use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    const LEVEL_COUNTRY = 0;//国家
    const LEVEL_PROVINCE = 1;//省份
    const LEVEL_CITY = 2;//城市
    const LEVEL_AREA = 3;//区县

    const LIMIT_OFF = 0;//关闭
    const LIMIT_ON = 1;//开启

    public function __construct(array $attributes = [])
    {
        $this->timestamps = false;
        parent::__construct($attributes);
    }

    protected $guarded=[];
}
