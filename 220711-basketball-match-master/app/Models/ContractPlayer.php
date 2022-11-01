<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class ContractPlayer extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'contract_players';
    protected $guarded = [];


    public function getAvatarPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }

    public function getCoverPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }

    public function grades()
    {
        return $this->hasMany(ContractPlayerGrade::class, 'ct_player_id')->orderByDesc('sort');
    }
}
