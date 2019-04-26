<?php

namespace Manadev\Docs\Docs\Traits;

use Manadev\Core\App;
use Manadev\Docs\Docs\Controllers\Web;
use Manadev\Docs\Docs\FileFinder;
use Manadev\Docs\Docs\Module;
use Manadev\Docs\Docs\BookDetector;
use Manadev\Framework\Http\Exceptions\NotFound;
use Manadev\Framework\Http\Responses;

trait DetectRouteTrait
{
    protected function around_findController(callable $proceed) {
        global $m_app; /* @var App $m_app */

        try {
            return $proceed();
        }
        catch (NotFound $e) {
            $bookDetector = $m_app[BookDetector::class]; /* @var BookDetector $bookDetector */
            $fileFinder = $m_app[FileFinder::class]; /* @var FileFinder $fileFinder */
            $module = $m_app->modules['Manadev_Docs_Docs']; /* @var Module $module */
            $request = $m_app->request;

            if ($request->method != 'GET') {
                throw $e;
            }

            if (!($book = $bookDetector->detectBook())) {
                throw $e;
            }

            $module->book = $book;

            $filePath = mb_substr($request->route, mb_strlen($book->url_path));
            if (!($file = $fileFinder->findFile($filePath))) {
                throw $e;
            }

            $module->file = $file;

            return Web::new(['route' => '/_books/page', 'method' => 'bookPage', 'public' => true], null,
                $m_app->area_->controllers);
        }
    }
}