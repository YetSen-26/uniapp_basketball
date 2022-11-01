<?php

namespace App\Models\Admin;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class ViewMatchTeam extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'view_match_team';
    protected $guarded = [];
//
//    public function getALogoPathAttribute($value)
//    {
//        return $value ? asset($value) : '';
//    }
//
//    public function getBLogoPathAttribute($value)
//    {
//        return $value ? asset($value) : '';
//    }
}
