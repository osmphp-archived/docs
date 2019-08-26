<?php

namespace Osm\Docs\Docs\Views;

use Osm\Core\App;
use Osm\Docs\Docs\Book;
use Osm\Docs\Docs\Hints\JsConfigHint;
use Osm\Docs\Docs\Module;
use Osm\Docs\Docs\Page;
use Osm\Framework\Views\JsConfig;
use Osm\Framework\Views\View;

/**
 * @property Page $page @required
 * @property JsConfig|JsConfigHint $js_config @required
 * @property Module $doc_module @required
 * @property Book $book @required
 */
class Html extends View
{
    public $template = 'Osm_Docs_Docs.html';

    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'doc_module': return $m_app->modules['Osm_Docs_Docs'];
            case 'book': return $this->doc_module->book;
        }

        return parent::default($property);
    }
}