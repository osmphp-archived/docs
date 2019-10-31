<?php

namespace Osm\Docs\Tree;

use Osm\Core\Modules\BaseModule;
use Osm\Docs\Docs\Controllers\Web as PageController;

class Module extends BaseModule
{
    public $hard_dependencies = [
        'Osm_Docs_Docs',
    ];

    public $traits = [
        PageController::class => Traits\PageControllerTrait::class,
    ];
}