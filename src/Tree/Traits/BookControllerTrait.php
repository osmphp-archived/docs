<?php

namespace Osm\Docs\Tree\Traits;

use Osm\Core\App;
use Osm\Docs\Docs\Module;
use Osm\Framework\Layers\Layout;

trait BookControllerTrait
{
    protected function around_bookPage(callable $proceed) {
        global $osm_app; /* @var App $osm_app */

        $docModule = $osm_app->modules['Osm_Docs_Docs']; /* @var Module $docModule */

        /* @var Layout $layout */
        $layout = $proceed();

        if (!($layout instanceof Layout)) {
            return $layout;
        }

        $layout->load([
            '#tree' => ['book' => $docModule->book, 'current_page' => $docModule->page],
        ]);

        return $layout;
    }
}