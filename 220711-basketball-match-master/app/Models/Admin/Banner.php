<?php

namespace App\Models\Admin;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
	use HasDateTimeFormatter;
    protected $guarded=[];
}
