<?php

namespace Osm\Docs\Tree;

use Osm\Core\Modules\BaseModule;
use Osm\Docs\Docs\Controllers\BookController;

class Module extends BaseModule
{
    public $hard_dependencies = [
        'Osm_Docs_Docs',
    ];

    public $traits = [
        BookController::class => Traits\BookControllerTrait::class,
    ];
}