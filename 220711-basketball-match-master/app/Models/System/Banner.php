<?php

namespace App\Models\System;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
	use HasDateTimeFormatter;
    protected $guarded=[];

    public function getImgPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }
}
