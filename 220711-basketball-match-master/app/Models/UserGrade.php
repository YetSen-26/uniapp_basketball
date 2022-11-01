<?php

namespace App\Models;

use App\Models\Admin\Competition;
use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class UserGrade extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'user_grade';
    protected $guarded = [];

//    public function getCoverPathAttribute($value)
//    {
//        return $value ? asset($value) : '';
//    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function ownerTeam()
    {
        return $this->belongsTo(\App\Models\Admin\MatchTeam::class, 'owner_team_id', 'id');
    }
}
