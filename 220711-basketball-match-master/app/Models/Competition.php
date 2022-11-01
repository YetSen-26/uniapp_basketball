<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'competition';
    protected $guarded = [];

    public function getCoverPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }
}
