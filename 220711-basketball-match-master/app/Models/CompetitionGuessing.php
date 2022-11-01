<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class CompetitionGuessing extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'competition_guessing';
    protected $guarded = [];

    public function getTeamALogoPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }

    public function getTeamBLogoPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }
}
