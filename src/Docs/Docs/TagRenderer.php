<?php

namespace Osm\Docs\Docs;

use Osm\Core\App;
use Osm\Core\Exceptions\NotSupported;
use Osm\Core\Object_;

/**
 * @see \Osm\Docs\Docs\Tag::$name @handler
 *
 * @property Module $module @required
 * @property Book $book @required
 *
 * @property Page $page @temp
 * @property Tag $tag @temp
 * @property string $text @temp
 * @property array $args @temp
 */
class TagRenderer extends Object_
{
     protected function default($property) {
        global $osm_app; /* @var App $osm_app */

        switch ($property) {
            case 'module': return $osm_app->modules['Osm_Docs_Docs'];
            case 'book': return $this->module->book;
        }
        return parent::default($property);
    }

    /**
     * @param Page $page
     * @param Tag $tag
     * @param string $text
     * @param array $args
     * @return string
     */
    public function render(Page $page, Tag $tag, $text, $args) {
        $this->page = $page;
        $this->tag = $tag;
        $this->text = $text;
        $this->args = $args;

        switch ($tag->name) {
            case 'toc': return $this->renderToc();
            case 'child_pages': return $this->renderChildPages();
            default:
                throw new NotSupported(osm_t("Tag ':tag' not supported", ['tag' => $tag->name]));
        }
    }

    protected function renderToc() {
        $result = "\n";
        foreach (explode("\n", $this->text) as $line) {
            if (!preg_match(Page::HEADER_PATTERN, $line, $match)) {
                continue;
            }

            $depth = strlen($match['depth']) - 2;
            if ($depth < 0) {
                continue;
            }

            if (isset($this->args['depth']) && $depth >= $this->args['depth']) {
                continue;
            }

            if (!isset($match['attributes'])) {
                continue;
            }

            if (!preg_match(Page::ID_PATTERN, $match['attributes'], $idMatch)) {
                continue;
            }

            $title = trim($match['title']);

            $result .= str_repeat(' ', $depth * 4);
            $result .= "* [" . $title . "](#{$idMatch['id']})\n";
        }

        return "{$result}\n";
    }

    protected function renderChildPages() {
        return $this->doRenderChildPages($this->page);
    }

    protected function doRenderChildPages(Page $parentPage, $depth = 0) {
        if (isset($this->args['depth']) && $depth >= $this->args['depth']) {
            return '';
        }

        $result = '';

        foreach ($parentPage->child_pages as $page) {
            $result .= str_repeat(' ', $depth * 4);
            $result .= "* [" . $page->original_title . "]({$page->url})\n";
            $result .= $this->doRenderChildPages($page, $depth + 1);
        }

        return $result;
    }
}