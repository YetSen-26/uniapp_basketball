<?php


namespace App\Models;


use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'category';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $guarded=[];
}
