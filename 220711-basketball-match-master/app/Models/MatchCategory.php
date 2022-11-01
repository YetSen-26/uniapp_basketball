<?php


namespace App\Models;


use App\Models\Admin\Competition;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class MatchCategory extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'match_category';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $guarded=[];

    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id', 'id');
    }
}
