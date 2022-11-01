<?php

namespace App\Models\Admin;

use App\Models\ContractPlayerGrade;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class ContractPlayer extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'contract_players';
    protected $guarded = [];

    public function grades()
    {
        return $this->hasMany(ContractPlayerGrade::class, 'ct_player_id')->orderByDesc('sort');
    }
}
