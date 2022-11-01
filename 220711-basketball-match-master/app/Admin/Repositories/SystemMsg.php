<?php

namespace App\Admin\Repositories;

use App\Models\System\SystemMsg as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SystemMsg extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
