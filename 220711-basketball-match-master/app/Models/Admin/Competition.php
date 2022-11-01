<?php

namespace App\Models\Admin;

use App\Models\Category;
use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'competition';
    protected $guarded = [];


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
