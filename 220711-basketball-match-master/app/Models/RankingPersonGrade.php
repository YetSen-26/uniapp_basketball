<?php

namespace App\Models;

use App\Models\ContractPlayerGrade;
use App\Models\Ranking;
use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class RankingPersonGrade extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'ranking_person_grade';
    protected $guarded = [];

}
