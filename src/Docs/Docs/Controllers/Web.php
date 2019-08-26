<?php

namespace Osm\Docs\Docs\Controllers;

use Osm\Core\App;
use Osm\Docs\Docs\Book;
use Osm\Docs\Docs\Hints\JsConfigHint;
use Osm\Docs\Docs\Page;
use Osm\Docs\Docs\Module;
use Osm\Framework\Http\Controller;
use Osm\Framework\Http\Responses;
use Osm\Framework\Views\JsConfig;

/**
 * @property Module $module @required
 * @property Page $page @required
 * @property Responses $responses @required
 * @property Book $book @required
 * @property JsConfigHint|JsConfig $js_config @required
 */
class Web extends Controller
{
    protected function default($property) {
        global $osm_app; /* @var App $osm_app */

        switch ($property) {
            case 'module': return $osm_app->modules['Osm_Docs_Docs'];
            case 'page': return $this->module->page;
            case 'responses': return $osm_app[Responses::class];
            case 'book': return $this->module->book;
            case 'js_config': return $osm_app[JsConfig::class];
        }
        return parent::default($property);
    }

    public function bookPage() {
        if ($this->page->type == Page::REDIRECT) {
            return $this->responses->redirect($this->page->redirect_to_url);
        }

        $this->js_config->book = (object)$this->book->getJsConfig();

        return m_layout('books_page', [
            '#page' => ['title' => $this->page->title],
            '#breadcrumbs' => ['page' => $this->page],
            '#html' => ['page' => $this->page],
        ]);
    }

    public function image() {
        return $this->responses->image($this->module->book->file_path . $this->module->image);
    }
}