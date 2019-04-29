<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\App;
use Manadev\Core\Exceptions\NotFound;
use Manadev\Core\Object_;
use Manadev\Framework\Http\Request;
use Manadev\Framework\Http\UrlGenerator;

/**
 * @property string $file_path @required @part
 * @property string $url_path @required @part
 * @property string $suffix @part Typical values: null, '/', 'html', ''
 * @property string $suffix_ @required
 *
 * @property UrlGenerator $url_generator @required
 * @property Request $request @required
 *
 * @see \Manadev\DocHost\Books\Module:
 *      @property int $id @required @part
 *      @property int $customer @required @part
 */
class Book extends Object_
{
    /**
     * @var Page[]
     */
    protected $pages = [];

    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'suffix_': return $this->getSuffix();
            case 'url_generator': return $m_app[UrlGenerator::class];
            case 'request': return $m_app->request;
        }

        return parent::default($property);
    }

    protected function getSuffix() {
        if (!$this->suffix) {
            return '';
        }

        if ($this->suffix == '/' || mb_strpos($this->suffix, '.') === 0) {
            return $this->suffix;
        }

        return '.' . $this->suffix;
    }

    /**
     * @param string $url relative to book URL domain and path
     * @param bool $required If true, "not found" and "redirect" result in NotFound exception
     * @return Page
     */
    public function getPage($url, $required = true) {
        if (!array_key_exists($url, $this->pages)) {
            $this->pages[$url] = $this->doGetPage($url);
        }

        if ($required && (!$this->pages[$url] || $this->pages[$url]->type === page::REDIRECT)) {
            throw new NotFound(m_("Page ':name' not found", ['name' => $url]));
        }

        return $this->pages[$url];
    }

    /**
     * @param string $url relative to book URL domain and path
     * @return Page
     */
    protected function doGetPage($url) {
        // handle home page URL. In the end we either return found home page or continue

        if ($url === '/') {
            $filename = 'index.md';
            if (!is_file("{$this->file_path}/{$filename}")) {
                return Page::new(['type' => Page::PLACEHOLDER], $url, $this);
            }

            return Page::new(['filename' => "{$this->file_path}/{$filename}"], $url, $this);
        }

        // handle suffix. In the end we either return that redirect to page with correct suffix is needed, return
        // that page doesn't exist to continue with $url being without the suffix

        if ($this->suffix_) {
            if (mb_strrpos($url, $this->suffix_) !== mb_strlen($url) - mb_strlen($this->suffix_)) {
                // page URL doesn't end with configured suffix '/' or '.html'. Return redirect to same URL
                // with added suffix if page will be found

                $redirectTo = $url . $this->suffix_;

                if (!$this->getPage($redirectTo)) {
                    return null;
                }

                return Page::new(['type' => Page::REDIRECT, 'redirect_to' => $redirectTo], $url, $this);
            }

            // page URL ends with configured suffix '/' or '.html'. Remove suffix from URL
            $url = mb_substr($url, 0, mb_strlen($url) - mb_strlen($this->suffix_));
        }
        elseif (mb_strrpos($url, '/') === mb_strlen($url) - mb_strlen('/')) {
            // no suffix configured but URL ends with '/'. Redirect to URL without '/' if such page exists
            $redirectTo = mb_substr($url, 0, mb_strlen($url) - mb_strlen('/'));

            if (!$this->getPage($redirectTo)) {
                return null;
            }

            return Page::new(['type' => Page::REDIRECT, 'redirect_to' => $redirectTo], $url, $this);
        }

        if ($url === '/index') {
            return Page::new(['type' => Page::REDIRECT, 'redirect_to' => '/'], $url . $this->suffix_, $this);
        }

        // handle page path. There should always be at least one '/' in URL as all page URLs start with '/'.
        // If underlying directory doesn't exist we return that page doesn't exist

        $pos = mb_strrpos($url, '/');
        $path = $this->file_path . mb_substr($url, 0, $pos);
        $filename = mb_substr($url, $pos + 1);

        if (!is_dir($path) || !$filename) {
            return null;
        }

        // find file with preceding sort order. If we find one, return that page exists

        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }

            if (preg_match("/(?:\\d+-)?" . preg_quote($filename) . "\\.md/u", $fileInfo->getFilename())) {
                return Page::new(['filename' => "{$path}/{$fileInfo->getFilename()}"],
                    $url . $this->suffix_, $this);
            }
        }

        if (is_dir("{$path}/{$filename}")) {
            return Page::new(['type' => Page::PLACEHOLDER], $url, $this);
        }

        // finally, if file is not found, return null to indicate that page doesn't exist
        return null;
    }

    /**
     * @param Page[] $pages
     * @return Page[]
     */
    public function sortPages($pages) {
        uasort($pages, function(Page $a, Page $b) {
            if ($a->type != $b->type) {
                if ($a->type < $b->type) return -1;
                if ($a->type > $b->type) return 1;
                return 0;
            }

            if ($a->type !== Page::PAGE) {
                if ($a->name < $b->name) return -1;
                if ($a->name > $b->name) return 1;
                return 0;
            }

            if ($a->filename < $b->filename) return -1;
            if ($a->filename > $b->filename) return 1;
            return 0;
        });

        return $pages;
    }

    public function getPageUrl($name) {
        return $this->url_generator->rawUrl('GET ' . $this->url_path . $name, $this->request->query);
    }
}