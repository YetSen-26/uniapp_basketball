<?php

namespace App\Models\Admin;

use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class AdminUser extends Administrator
{
	use HasDateTimeFormatter;
    protected $guarded=[];

    protected $table = 'users';
}
