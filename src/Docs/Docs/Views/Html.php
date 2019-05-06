<?php

namespace Manadev\Docs\Docs\Views;

use Manadev\Core\App;
use Manadev\Docs\Docs\Book;
use Manadev\Docs\Docs\Hints\JsConfigHint;
use Manadev\Docs\Docs\Module;
use Manadev\Docs\Docs\Page;
use Manadev\Framework\Views\JsConfig;
use Manadev\Framework\Views\View;

/**
 * @property Page $page @required
 * @property JsConfig|JsConfigHint $js_config @required
 * @property Module $doc_module @required
 * @property Book $book @required
 */
class Html extends View
{
    public $template = 'Manadev_Docs_Docs.html';

    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'doc_module': return $m_app->modules['Manadev_Docs_Docs'];
            case 'book': return $this->doc_module->book;
        }

        return parent::default($property);
    }

    public function rendering() {
        $this->js_config->book = (object)$this->book->getJsConfig();
        parent::rendering();
    }
}