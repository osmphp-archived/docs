<?php

namespace Manadev\Docs\Docs\Advices;

use Manadev\Docs\Docs\Controllers\Web;
use Manadev\Docs\Docs\File;
use Manadev\Docs\Docs\FileFinder;
use Manadev\Core\App;
use Manadev\Framework\Http\Advice;
use Manadev\Framework\Http\Request;

/**
 * @property Request $request @required
 * @property FileFinder $file_finder @required
 */
class DetectSeoRoute extends Advice
{
    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'request': return $m_app->request;
            case 'file_finder': return $m_app[FileFinder::class];
        }
        return parent::default($property);
    }

    public function around(callable $next) {
        global $m_app; /* @var App $m_app */

        if ($this->request->method != 'GET') {
            return $next();
        }

        $pageUrl = $this->request->route;
        if (!($filename = $this->file_finder->findFile($pageUrl))) {
            return $next();
        }

        $m_app->controller = Web::new([
            'method' => 'show',
            'public' => true,
            'file' => File::new(['name' => $filename]),
        ], null, $m_app->area_->controllers);

        return $next();
    }
}