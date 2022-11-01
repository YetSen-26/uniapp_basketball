<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class ContractPlayerGrade extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'contract_player_grade';
    protected $guarded = [];
}
