<?php

namespace App\Models\System;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

//签到表
class SignIn extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'sign_in';

    public $timestamps = false;

    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
