<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class CompetitionMatch extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'competition_match';
    protected $guarded = [];

    public function getTeamALogoPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }

    public function getTeamBLogoPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }

    public function teamA(){
        return $this->belongsTo(MatchTeam::class,'team_a_id','id');
    }

    public function teamB(){
        return $this->belongsTo(MatchTeam::class,'team_b_id','id');
    }
}
