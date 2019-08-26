<?php

namespace Osm\Docs\Docs\Traits;

use Osm\Core\App;
use Osm\Docs\Docs\Controllers\Web;
use Osm\Docs\Docs\Module;
use Osm\Docs\Docs\BookDetector;
use Osm\Framework\Http\Exceptions\NotFound;

trait DetectRouteTrait
{
    protected function around_findController(callable $proceed) {
        global $m_app; /* @var App $m_app */

        try {
            return $proceed();
        }
        catch (NotFound $e) {
            $bookDetector = $m_app[BookDetector::class]; /* @var BookDetector $bookDetector */
            $module = $m_app->modules['Osm_Docs_Docs']; /* @var Module $module */
            $request = $m_app->request;

            if ($request->method != 'GET') {
                throw $e;
            }

            if (!($book = $bookDetector->detectBook())) {
                throw $e;
            }

            $module->book = $book;

            $filePath = mb_substr($request->route, mb_strlen($book->url_path));

            if (starts_with($filePath, '/.')) {
                throw $e;
            }

            if ($page = $book->getPage($filePath, false)) {
                $module->page = $page;
                return $m_app->area_->controllers['GET /_books/page'];
            }

            if ($book->isImage($filePath)) {
                $module->image = $filePath;
                return $m_app->area_->controllers['GET /_books/image'];
            }

            throw $e;
        }
    }
}