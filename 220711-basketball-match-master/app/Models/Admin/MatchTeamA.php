<?php

namespace App\Models\Admin;

use App\Models\MatchTeam;
use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class MatchTeamA extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'match_team_a';
    protected $guarded = [];


}
