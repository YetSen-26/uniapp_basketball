<?php

namespace App\Models\Admin;

use App\Models\ContractPlayerGrade;
use App\Models\Ranking;
use App\Models\RankingPersonGrade;
use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class RankingPerson extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'ranking_person';
    protected $guarded = [];

    public function ranking()
    {
        return $this->belongsTo(Ranking::class, 'ranking_id', 'id');
    }

    public function rpg()
    {
        return $this->hasMany(RankingPersonGrade::class, 'ranking_person_id','id')->orderByDesc('sort');
    }
}
