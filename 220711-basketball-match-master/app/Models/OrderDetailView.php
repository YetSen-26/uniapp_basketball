<?php

namespace App\Models;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class OrderDetailView extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'order_detail_view';
    protected $guarded = [];

    public function getCoverPathAttribute($value)
    {
        return $value ? asset($value) : '';
    }
}
