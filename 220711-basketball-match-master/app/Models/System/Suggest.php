<?php

namespace App\Models\System;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class Suggest extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'suggest';

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
