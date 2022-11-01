<?php

namespace App\Models\Admin;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class MatchTeam extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'match_team';
    protected $guarded = [];

}
