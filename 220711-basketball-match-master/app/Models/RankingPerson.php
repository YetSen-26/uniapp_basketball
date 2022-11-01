<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class RankingPerson extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'ranking_person';
    protected $guarded = [];

    public function getAvatarPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }

    public function ranking()
    {
        return $this->belongsTo(Ranking::class, 'ranking_id', 'id');
    }

    public function RankingPersonGrade()
    {
        return $this->hasMany(RankingPersonGrade::class, 'ranking_person_id','id')->orderByDesc('sort');
    }
}
