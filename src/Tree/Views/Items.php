<?php

namespace Osm\Docs\Tree\Views;

use Osm\Core\App;
use Osm\Docs\Docs\Page;
use Osm\Framework\Cache\Cache;
use Osm\Framework\Views\View;

/**
 * @property Tree $parent @required
 * @property string $cache_key @required
 * @property Cache $cache @required
 *
 * @property Page $page @temp
 * @property string $cached @temp
 */
class Items extends View
{
    public $template = 'Osm_Docs_Tree.items';

    protected function default($property) {
        global $osm_app; /* @var App $osm_app */

        switch ($property) {
            case 'cache': return $osm_app->cache;
            case 'cache_key': return "{$this->parent->book->cache_tag}|tree_html";
        }
        return parent::default($property);
    }

    public function rendering() {
        if ($this->page->parent_page) {
            return null;
        }

        $result = $this->cache->get($this->cache_key);
        $this->cached = $result !== null;
        return $result;
    }

    public function rendered($result) {
        if ($this->page->parent_page) {
            return $result;
        }

        if (!$this->cached) {
            $this->cache->put($this->cache_key, $result,
                ['books', $this->parent->book->cache_tag]);
        }

        return $result;
    }
}