<?php

namespace Osm\Docs\Docs\Traits;

use Osm\Core\App;
use Osm\Docs\Docs\BookDetector;
use Osm\Docs\Docs\Module;

trait DetectAreaTrait
{
    protected function around_around(callable $proceed, callable $next) {
        global $osm_app; /* @var App $osm_app */

        $request = $osm_app->request;
        $bookDetector = $osm_app[BookDetector::class]; /* @var BookDetector $bookDetector */
        $module = $osm_app->modules['Osm_Docs_Docs']; /* @var Module $module */

        if ($request->method != 'GET') {
            return $proceed($next);
        }

        if (!($module->book = $bookDetector->detectBook())) {
            return $proceed($next);
        }

        $osm_app->area = 'book';
        $request->base_url_path = $module->book->url_path;

        return $next();
    }
}