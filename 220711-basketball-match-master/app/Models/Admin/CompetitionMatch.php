<?php

namespace App\Models\Admin;

use App\Models\MatchCategory;
use App\Models\MatchTeam;
use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class CompetitionMatch extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'competition_match';
    protected $guarded = [];

    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id', 'id');
    }

    public function matchCategory()
    {
        return $this->belongsTo(MatchCategory::class, 'competition_id', 'id');
    }

    public function getUseraListAttribute($value)
    {
        return explode(',',$value);
    }

    public function setUseraListAttribute($value)
    {
        $this->attributes['usera_list'] = implode(',',$value);
    }

    public function getUserbListAttribute($value)
    {
        return explode(',',$value);
    }

    public function setUserbListAttribute($value)
    {
        $this->attributes['userb_list'] = implode(',',$value);
    }
}
