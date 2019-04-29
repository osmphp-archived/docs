<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\App;
use Manadev\Core\Exceptions\NotSupported;
use Manadev\Core\Object_;

/**
 * @see \Manadev\Docs\Docs\Tag::$name @handler
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
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'module': return $m_app->modules['Manadev_Docs_Docs'];
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
                throw new NotSupported(m_("Tag ':tag' not supported", ['tag' => $tag->name]));
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
            $result .= "* [" . $page->title . "]({$page->url})\n";
            $result .= $this->doRenderChildPages($page, $depth + 1);
        }

        return $result;
    }
}