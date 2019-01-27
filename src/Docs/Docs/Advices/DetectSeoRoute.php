<?php

namespace Manadev\Docs\Docs\Advices;

use Manadev\Docs\Docs\Controllers\Web;
use Manadev\Docs\Docs\File;
use Manadev\Docs\Docs\Hints\SettingsHint;
use Manadev\Core\App;
use Manadev\Core\Profiler;
use Manadev\Framework\Http\Advice;
use Manadev\Framework\Http\Request;
use Manadev\Framework\Settings\Settings;

/**
 * @property Request $request @required
 * @property Settings|SettingsHint $settings @required
 * @property string $doc_root @required
 */
class DetectSeoRoute extends Advice
{
    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'request': return $m_app->request;
            case 'settings': return $m_app->settings;
            case 'doc_root': return $this->settings->doc_root;
        }
        return parent::default($property);
    }

    public function around(callable $next) {
        global $m_app; /* @var App $m_app */

        if ($this->request->method != 'GET') {
            return $next();
        }

        $pageUrl = $this->request->route;
        if (!($filename = $this->findFile($pageUrl))) {
            return $next();
        }

        $m_app->controller = Web::new([
            'method' => 'show',
            'public' => true,
            'file' => File::new(['name' => $filename]),
        ], null, $m_app->area_->controllers);

        return $next();
    }

    protected function findFile($pageUrl) {
        global $m_profiler; /* @var Profiler $m_profiler */

        if ($m_profiler) $m_profiler->start(__METHOD__, 'my_app');
        try {
            $path = $this->doc_root;
            foreach (explode('/', mb_substr($pageUrl, mb_strlen('/'))) as $part) {
                $part = $this->request->decode($part);
                if ($part === '') {
                    $path .= "/index.md";
                    continue;
                }

                $path = $this->findFileOrDirectory($path, $part);
            }

            return is_file($path) ? $path : null;
        }
        finally {
            if ($m_profiler) $m_profiler->stop(__METHOD__);
        }
    }

    protected function findFileOrDirectory($path, $part) {
        if (!is_dir($path)) {
            return "{$path}/{$part}";
        }

        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                if (preg_match("/(\\d+-)?" . preg_quote($part). "/u", $fileInfo->getFilename())) {
                    return "{$path}/{$fileInfo->getFilename()}";
                }
                continue;
            }

            if (preg_match("/(\\d+-)?" . preg_quote($part) . "\\.md/u", $fileInfo->getFilename())) {
                return "{$path}/{$fileInfo->getFilename()}";
            }
        }

        return "{$path}/{$part}";
    }
}