<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\App;
use Manadev\Core\Object_;
use Manadev\Core\Profiler;
use Manadev\Docs\Docs\Hints\SettingsHint;
use Manadev\Framework\Http\Request;
use Manadev\Framework\Settings\Settings;

/**
 * @property Request $request @required
 * @property Settings|SettingsHint $settings @required
 * @property string $doc_root @required
 */
class FileFinder extends Object_
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

    public function findFile($pageUrl) {
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