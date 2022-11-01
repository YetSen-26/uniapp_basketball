<?php

namespace App\Admin\Repositories;

use App\Models\PostImg as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class PostImg extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
