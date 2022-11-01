<?php

namespace App\Models\System;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class SystemMsg extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'system_msg';
    protected $guarded = [];

}
