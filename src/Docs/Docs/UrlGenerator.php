<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\App;
use Manadev\Core\Object_;
use Manadev\Framework\Http\UrlGenerator as HttpUrlGenerator;

/**
 * @property string $base_url @required
 * @property Module $module @required
 * @property Book $book @required
 * @property HttpUrlGenerator $http_url_generator @required
 */
class UrlGenerator extends Object_
{
     protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'base_url': return $m_app->request->base;
            case 'module': return $m_app->modules['Manadev_Docs_Docs'];
            case 'book': return $this->module->book;
            case 'http_url_generator': return $m_app[HttpUrlGenerator::class];
        }
        return parent::default($property);
    }

    public function generateUrl($filename) {
        return  $this->http_url_generator->rawUrl(
            'GET ' . $this->book->url_path . $this->generateRelativeUrl($filename),
            $this->http_url_generator->generateQuery('GET /__books/pages/'));
    }

    public function generateRelativeUrl($filename) {
        $filename = mb_substr($filename, mb_strlen($this->book->file_path) + 1);
        $result = '';

        foreach (explode('/', str_replace("\\", '/', $filename)) as $part) {
            $result .= '/';
            if ($part == 'index.md') {
                continue;
            }

            $result .= preg_replace("/(\\d+-)|(\\.md)/u", '', $part);
        }
        return $result;
    }
}