<?php

namespace Manadev\Docs\Docs\Controllers;

use Manadev\Core\App;
use Manadev\Docs\Docs\Book;
use Manadev\Docs\Docs\Page;
use Manadev\Docs\Docs\Module;
use Manadev\Framework\Http\Controller;
use Manadev\Framework\Http\Responses;
use Manadev\Framework\Http\UrlGenerator;

/**
 * @property Module $module @required
 * @property Page $page @required
 * @property Responses $responses @required
 * @property UrlGenerator $url_generator @required
 * @property Book $book @required
 */
class Web extends Controller
{
    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'module': return $m_app->modules['Manadev_Docs_Docs'];
            case 'page': return $this->module->page;
            case 'responses': return $m_app[Responses::class];
            case 'url_generator': return $m_app->url_generator;
            case 'book': return $this->module->book;
        }
        return parent::default($property);
    }

    public function bookPage() {
        if ($this->page->redirect) {
            return $this->responses->redirect($this->url_generator->rawUrl(
                'GET ' . $this->book->url_path . $this->page->redirect,
                $this->request->query));
        }

        return m_layout('books_page', [
            '#page' => ['title' => $this->page->title],
            '#breadcrumbs' => ['page' => $this->page],
            '#html' => ['page' => $this->page],
        ]);
    }
}