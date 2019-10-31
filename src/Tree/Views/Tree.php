<?php

namespace Osm\Docs\Tree\Views;

use Osm\Docs\Docs\Book;
use Osm\Docs\Docs\Page;
use Osm\Framework\Views\View;

/**
 * @property Items $renderer @required @part
 * @property string $contents_button @part
 * @property string $drawer @part
 *
 * @property Page $current_page @required
 * @property Book $book @required
 */
class Tree extends View
{
    public $template = 'Osm_Docs_Tree.tree';
    public $view_model = 'Osm_Docs_Tree.Tree';

    /**
     * @required @part
     * @var string
     */
    public $items_template = 'Osm_Docs_Tree.items';

    public function rendering() {
        $this->model = osm_merge([
            'expand_collapse_state' => $this->getExpandCollapseState(),
            'contents_button' => $this->contents_button,
            'drawer' => $this->drawer,
            'hide_if_window_width_less_than' => 768,
        ], $this->model ?: []);
    }

    protected function getExpandCollapseState() {
        $result = [];

        for ($page = $this->current_page; $page; $page = $page->parent_page) {
            $result[$page->url] = true;
        }

        return $result;
    }
}