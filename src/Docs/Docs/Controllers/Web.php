<?php

namespace Manadev\Docs\Docs\Controllers;

use Manadev\Core\App;
use Manadev\Docs\Docs\File;
use Manadev\Docs\Docs\Module;
use Manadev\Docs\Docs\Views\DocPage;
use Manadev\Framework\Http\Controller;

/**
 * @property Module $module @required
 * @property File $file @required
 */
class Web extends Controller
{
    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'module': return $m_app->modules['Manadev_Docs_Docs'];
            case 'file': return $this->module->file;
        }
        return parent::default($property);
    }

    public function bookPage() {
        return m_layout('books_page', [
            '#page' => ['title' => $this->file->title],
            '#breadcrumbs' => ['file' => $this->file],
            '#html' => ['file' => $this->file],
        ]);
    }
}