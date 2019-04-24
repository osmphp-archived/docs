<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\App;
use Manadev\Core\Object_;
use Manadev\Core\Profiler;
use Manadev\Framework\Http\Request;

/**
 * @property Module $module @required
 * @property Request $request @required
 * @property Book $book @required
 */
class FileFinder extends Object_
{
    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'request': return $m_app->request;
            case 'module': return $m_app->modules['Manadev_Docs_Docs'];
            case 'book': return $this->module->book;
        }
        return parent::default($property);
    }

    /**
     * @param string $pageUrl
     * @return File
     */
    public function findFile($pageUrl) {
        global $m_profiler; /* @var Profiler $m_profiler */

        if ($m_profiler) $m_profiler->start(__METHOD__, 'my_app');

        try {
            $path = $this->book->file_path;
            foreach (explode('/', mb_substr($pageUrl, mb_strlen('/'))) as $part) {
                $part = $this->request->decode($part);
                if ($part === '') {
                    $path .= "/index.md";
                    continue;
                }

                $path = $this->findFileOrDirectory($path, $part);
            }

            return is_file($path) ? File::new(['name' => $path]) : null;
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