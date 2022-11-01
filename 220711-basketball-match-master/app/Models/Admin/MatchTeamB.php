<?php

namespace App\Models\Admin;

use App\Models\MatchTeam;
use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class MatchTeamB extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'match_team_b';
    protected $guarded = [];


}
