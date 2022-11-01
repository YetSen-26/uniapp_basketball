<?php

namespace App\Admin\Repositories;

use App\Models\Collect as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Collect extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
