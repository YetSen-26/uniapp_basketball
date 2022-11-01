<?php

namespace App\Models\Admin;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class AdminRoleUser extends Model
{
	use HasDateTimeFormatter;
    protected $guarded=[];

    protected $table = 'admin_role_users';
}
