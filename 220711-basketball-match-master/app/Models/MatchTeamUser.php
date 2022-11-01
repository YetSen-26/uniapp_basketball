<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class MatchTeamUser extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'match_team_user';
    protected $guarded = [];

}
