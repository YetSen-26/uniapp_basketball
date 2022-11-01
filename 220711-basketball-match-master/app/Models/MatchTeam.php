<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class MatchTeam extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'match_team';
    protected $guarded = [];

    public function getLogoPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }

}
