<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class CompetitionApply extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'competition_apply';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function competition()
    {
        return $this->belongsTo(\App\Models\Admin\Competition::class, 'competition_id', 'id');
    }
}
