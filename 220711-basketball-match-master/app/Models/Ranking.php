<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'ranking';
    protected $guarded = [];

}
