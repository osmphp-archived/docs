<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\App;
use Manadev\Core\Exceptions\NotSupported;
use Manadev\Core\Object_;

/**
 * @see \Manadev\Docs\Docs\Tag::$name @handler
 *
 * @property UrlGenerator $url_generator @required
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
            case 'url_generator': return $m_app[UrlGenerator::class];
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
        if ($this->page->directory) {
            return $this->renderChildPagesFromDirectory($this->page->name);
        }

        if (basename($this->page->name) == 'index.md') {
            return $this->renderChildPagesFromDirectory(dirname($this->page->name));
        }

        $path = mb_substr($this->page->name, 0, mb_strlen($this->page->name) - mb_strlen('.md'));
        if (!is_dir($path)) {
            return '';
        }

        return $this->renderChildPagesFromDirectory($path);
    }

    protected function renderChildPagesFromDirectory($path, $depth = 0) {
        if (isset($this->args['depth']) && $depth >= $this->args['depth']) {
            return '';
        }

        $result = '';
        $pages = $this->findChildPages($path);
        ksort($pages);

        foreach ($pages as $filename => $title) {
            $result .= str_repeat(' ', $depth * 4);
            $result .= "* [" . $title . "]({$this->url_generator->generateUrl($filename)})\n";

            if (basename($filename) == 'index.md') {
                $result .= $this->renderChildPagesFromDirectory(dirname($filename), $depth + 1);
            }
        }

        return $result;
    }

    protected function findChildPages($path) {
        $result = [];

        $directories = [];

        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $directories[$fileInfo->getPathname()] = $fileInfo->getFilename();
                continue;
            }

            if ($fileInfo->getFilename() == 'index.md') {
                continue;
            }

            if (strtolower(pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION)) != 'md') {
                continue;
            }

            $page = Page::new(['name' => $fileInfo->getPathname()]);
            $result[$page->name] = $page->title;
        }

        foreach (array_keys($directories) as $directory) {
            foreach (array_keys($result) as $filename) {
                if (preg_match("/(\\d+-)?" . preg_quote(basename($directory)) . "\\.md/u", basename($filename))) {
                    unset($directories[$directory]);
                    break;
                }
            }
        }

        return array_merge($result, $directories);
    }
}