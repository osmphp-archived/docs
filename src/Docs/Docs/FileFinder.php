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
     * @return Page
     */
    public function findFile($pageUrl) {
        global $m_profiler; /* @var Profiler $m_profiler */

        if ($m_profiler) $m_profiler->start(__METHOD__, 'my_app');

        try {
            $path = $this->book->file_path;
            $redirect = $this->removeSuffix($pageUrl);
            $parts = explode('/', mb_substr($pageUrl, mb_strlen('/')));

            foreach (array_slice($parts, 0, count($parts) - 1) as $i => $part) {
                $path .= '/' . $this->request->decode($part);
            }

            $part = $this->request->decode($parts[count($parts) - 1]);
            $path = $this->findFileInDirectory($path, $part, $found);

            if ($found) {
                return Page::new(['file_name' => $path, 'redirect' => $redirect]);
            }

            if (is_dir($path)) {
                return Page::new(['file_name' => $path, 'redirect' => $redirect, 'directory' => true]);
            }

            return null;
        }
        finally {
            if ($m_profiler) $m_profiler->stop(__METHOD__);
        }
    }

    protected function findFileInDirectory($path, $part, &$found) {
        $found = false;

        if (!is_dir($path)) {
            return "{$path}/{$part}";
        }

        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }

            if (preg_match("/(\\d+-)?" . preg_quote($part) . "\\.md/u", $fileInfo->getFilename())) {
                $found = true;
                return "{$path}/{$fileInfo->getFilename()}";
            }
        }

        return "{$path}/{$part}";
    }

    protected function removeSuffix(&$pageUrl) {
        if ($pageUrl === '/') {
            // for home page, tell to search for 'index.md'
            $pageUrl = '/index';
            return null;
        }

        if ($suffix = $this->book->suffix_) {
            if (mb_strrpos($pageUrl, $suffix) === mb_strlen($pageUrl) - mb_strlen($suffix)) {
                // page URL ends with configured suffix '/' or '.html'. Remove suffix from URL and tell that redirect
                // is not needed
                $pageUrl = mb_substr($pageUrl, 0, mb_strlen($pageUrl) - mb_strlen($suffix));
                return null;
            }

            // page URL doesn't end with configured suffix '/' or '.html'. Don't do anything to URL and tell the
            // system to redirect to same URL with added suffix if page will be found
            return $pageUrl . $suffix;
        }

        if (mb_strrpos($pageUrl, '/') === mb_strlen($pageUrl) - mb_strlen('/')) {
            // no suffix configured but URL ends with '/'. Remove '/' from URL and indicate redirect to URL
            // without '/'
            $pageUrl = mb_substr($pageUrl, 0, mb_strlen($pageUrl) - mb_strlen('/'));
            return $pageUrl;
        }

        // no suffix is configured and no suffix is found. Process URL as is, redirect is not needed
        return null;
    }
}