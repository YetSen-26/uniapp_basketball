<?php

namespace App\Admin\Repositories;

use App\Models\System\Suggest as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Suggest extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
