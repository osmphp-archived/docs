<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\App;
use Manadev\Core\Object_;
use Manadev\Framework\Http\Request;
use Michelf\MarkdownExtra;
use Manadev\Framework\Http\UrlGenerator as HttpUrlGenerator;

/**
 * Properties applicable to all page types
 *
 * @property string $name @required @part URL relative to book URL domain and path
 * @property Book $parent @required
 *
 * Properties applicable only to REDIRECT type:
 *
 * @property string $redirect_to @part If not-empty, means that page does exist but URL should be different
 *      (most often there is extra '/')
 * @property string $redirect_to_url
 *
 * Properties applicable to PAGE and PLACEHOLDER types:
 *
 * @property string $filename @required @part File name of this book page
 * @property string $title @required @part
 * @property string $html @required @part
 * @property string $text @required @part
 * @property string $original_text @required @part
 * @property int $level @required @part
 *
 * @property Page $parent_page
 * @property Page[] $parent_pages @required
 * @property Page[] $sibling_pages @required
 * @property Page[] $child_pages @required
 * @property string $url @required
 * @property Image[] $images @required
 *
 * @property string $base_url @required
 * @property string $public_path @required
 * @property Module $module @required
 * @property Tags|Tag[] $tags @required
 * @property TagRenderer $tag_renderer @required
 * @property TypeConverter $type_converter @required
 * @property HttpUrlGenerator $url_generator @required
 * @property Request $request @required
 */
class Page extends Object_
{
    // types
    const PAGE = 'page';
    const PLACEHOLDER = 'placeholder';
    const REDIRECT = 'redirect';

    /**
     * @required @part
     *
     * @var string
     */
    public $type = self::PAGE;

    const H1_PATTERN = "/^#\\s*(?<title>[^#{]+)/u";
    const HEADER_PATTERN = "/^(?<depth>#+)\\s*(?<title>[^#{\\r\\n]+)#*[ \\t]*(?:{(?<attributes>[^}\\r\\n]*)})?\\r?$/mu";
    const IMAGE_LINK_PATTERN = "/!\\[(?<description>[^\\]]*)\\]\\((?<url>[^\\)]+)\\)/u";
    const TAG_PATTERN = "/{{\\s*(?<tag>[^ }]*)(?<args>.*)}}/u";
    const ARG_PATTERN = "/(?<key>[a-z0-9_]+)\\s*=\\s*\"(?<value>[^\"]*)\"/u";
    const ID_PATTERN = "/#(?<id>[^ ]+)/u";
    const LINK_PATTERN = "/\\[(?<title>[^\\]]+)\\]\\((?<url>[^\\)]+)\\)/u";
    const CHARS_BEING_REPLACED = [
        // characters listed below when found in SEOified text are replaced by SEO friendly characters from
        // REPLACEMENTS array. For example, ' ' ir replaced with '-'
        ' ', '\\', '/',

        // characters listed below when found in SEOified text are ignored, i.e. not put into generated URL
        '`', '"', '\'', '(', ')', '.', ',', '?', '!',
    ];
    const REPLACEMENTS = ['-', '-', '-'];
    const IMAGE_EXTENSIONS = ['png', 'jpg', 'gif'];

    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'title': return $this->getTitle();
            case 'original_text': return $this->type == static::PLACEHOLDER
                ? "# " . basename($this->name). " #\n\n{{ child_pages depth=\"1\" }}\n"
                : file_get_contents($this->filename);
            case 'text': return $this->transformText($this->original_text);
            case 'html': return $this->transformHtml(MarkdownExtra::defaultTransform($this->text));
            case 'level': return $this->getLevel();
            case 'parent_page': return $this->getParentPage();
            case 'parent_pages': return $this->getParentPages();
            case 'sibling_pages': return $this->getSiblingPages();
            case 'child_pages': return $this->getChildPages();
            case 'images': return $this->getImages();
            case 'url': return $this->parent->getPageUrl($this->name);
            case 'redirect_to_url': return $this->parent->getPageUrl($this->redirect_to);

            case 'base_url': return $m_app->request->base;
            case 'public_path': return $m_app->path($m_app->public_path);
            case 'module': return $m_app->modules['Manadev_Docs_Docs'];
            case 'tags': return $this->module->tags;
            case 'tag_renderer': return $m_app[TagRenderer::class];
            case 'type_converter': return $m_app[TypeConverter::class];
            case 'book': return $this->module->book;
            case 'request': return $m_app->request;
        }
        return parent::default($property);
    }

    protected function getTitle() {
        foreach (explode("\n", $this->original_text) as $line) {
            if (preg_match(static::H1_PATTERN, $line, $match)) {
                return trim($match['title']);
            }
        }

        return '';
    }

    protected function transformText($text) {
        $text = $this->makeImagesPublic($text);
        $text = $this->assignHeadingIds($text);
        $text = $this->processTags($text);
        return $text;
    }

    protected function makeImagesPublic($text) {
        return preg_replace_callback(static::IMAGE_LINK_PATTERN, function($match) use ($text) {
            if (!($filename = $this->makeImagePublic($match['url']))) {
                return $match[0];
            }

            $imageUrl = $this->base_url .
                (env('APP_ENV') == 'testing' ? '/testing' : '') .
                str_replace('\\', '/', mb_substr($filename, mb_strlen($this->public_path)));
            return "![{$match['description']}]({$imageUrl})";
        }, $text);
    }

    protected function makeImagePublic($imageUrl) {
        if (!in_array(strtolower(pathinfo($imageUrl, PATHINFO_EXTENSION)), static::IMAGE_EXTENSIONS)) {
            return null;
        }

        $source = $this->findSourceImage($imageUrl);
        if (!is_file($source)) {
            return null;
        }

        $target = $this->generateImageTargetFilename($imageUrl);
        if (!is_file($target)) {
            copy($source, m_make_dir_for($target));
            return $target;
        }

        if (filemtime($source) > filemtime($target)) {
            copy($source, m_make_dir_for($target));
        }
        return $target;
    }

    protected function findSourceImage($imageUrl) {
        $result = dirname($this->filename);

        foreach (explode('/', $imageUrl) as $part) {
            if ($part == '..') {
                $result = dirname($result);
                continue;
            }

            $result = "{$result}/{$part}";
        }

        return $result;
    }

    protected function generateImageTargetFilename($imageUrl) {
        $result = $this->public_path . '/images' . $this->parent->url_path;

        $path = mb_substr(dirname($this->filename), mb_strlen($this->parent->file_path));
        $path = str_replace('\\', '/', $path);

        // first element is removed as it is always empty
        foreach (array_slice(explode('/', $path), 1) as $part) {
            $result .= '/' . preg_replace('/^\d+-(.*)/u', '$1', $part);
        }

        foreach (explode('/', $imageUrl) as $part) {
            if ($part == '..') {
                $result = dirname($result);
                continue;
            }

            $result = "{$result}/{$part}";
        }

        return $result;
    }

    protected function processTags($text) {
        return preg_replace_callback(static::TAG_PATTERN, function($match) use ($text) {
            if (!($tag = $this->tags[$match['tag']] ?? null)) {
                return $match[0];
            }

            return $this->tag_renderer->render($this, $tag, $text, $this->parseArgs($match['args'], $tag)) ?? $match[0];
        }, $text);
    }

    /**
     * @param string $args
     * @param Tag $tag
     * @return array
     */
    protected function parseArgs($args, Tag $tag) {
        $result = [];

        if (!preg_match_all(static::ARG_PATTERN, $args, $matches)) {
            return $result;
        }

        foreach (array_keys($matches[0]) as $index) {
            $key = $matches['key'][$index];
            $value = $matches['value'][$index];

            if (!($type = $tag->parameters[$key] ?? null)) {
                continue;
            }

            $result[$key] = $this->type_converter->convert($type, $value);
        }

        return $result;
    }

    protected function assignHeadingIds($text) {
        $ids = [];

        return preg_replace_callback(static::HEADER_PATTERN, function($match) use (&$ids){
            $attributes = $match['attributes'] ?? '';
            if (mb_strpos($attributes, '#') !== false) {
                return $match[0];
            }

            $id = $this->generateId($match['title'], $ids);

            return "{$match['depth']} {$match['title']} {$match['depth']} {#{$id} {$attributes}}";
        }, $text);
    }

    protected function generateId($title, &$ids) {
        $key = $id = str_replace(static::CHARS_BEING_REPLACED, static::REPLACEMENTS,
            mb_strtolower(trim($title)));
        for ($suffix = 1; ; $suffix++) {
            $key = $suffix > 1 ? "$id-$suffix" : $id;
            if (!isset($ids[$key])) {
                break;
            }
        }

        $ids[$key] = true;
        return $key;
    }

    protected function transformHtml($html) {
        $html = $this->fixCodeBlocks($html);
        return $html;
    }

    protected function fixCodeBlocks($html) {
//        $html = str_replace('<pre> <code>', '<pre><code>', $html);
        $html = str_replace("\n</code>", '</code>', $html);
        return $html;
    }

    /**
     * @return Page[]
     */
    protected function getParentPages() {

        if ($this->name === '/') {
            return [];
        }

        $result = ['/' => $this->parent->getPage('/')];
        $url = rtrim($this->name, '/');
        for ($pos = mb_strpos($url, '/', 1); $pos !== false; $pos = mb_strpos($url, '/', $pos + 1)) {
            $name = mb_substr($url, 0, $pos) . $this->parent->suffix_;
            $result[] = $this->parent->getPage($name);
        }

        return $result;
    }

    protected function getParentPage() {
        if ($this->name === '/') {
            return null;
        }

        $url = rtrim($this->name, '/');
        $pos = mb_strrpos($url, '/', 1);

        if ($pos === false) {
            return $this->parent->getPage('/');
        }

        return $this->parent->getPage(mb_substr($url, 0, $pos) . $this->parent->suffix_);
    }

    protected function getChildPages() {
        $parentUrl = $this->name === '/'
            ? ''
            : mb_substr($this->name, 0, mb_strlen($this->name) - mb_strlen($this->parent->suffix_));
        $path = $this->parent->file_path . $parentUrl;
        $result = [];

        if (!is_dir($path)) {
            return $result;
        }

        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $name = "{$parentUrl}/{$fileInfo->getFilename()}{$this->parent->suffix_}";
                $result[$name] = $this->parent->getPage($name);
                continue;
            }

            if (!$parentUrl && $fileInfo->getFilename() == 'index.md') {
                continue;
            }

            if (preg_match("/(?:\\d+-)?(?<name>.*)\\.md/u", $fileInfo->getFilename(), $match)) {
                $name = "{$parentUrl}/{$match['name']}{$this->parent->suffix_}";
                $result[$name] = $this->parent->getPage($name);
            }
        }

        return $this->parent->sortPages($result);
    }

    protected function getSiblingPages() {
        if (!$this->parent_page) {
            return [];
        }

        return $this->parent_page->child_pages;
    }

    protected function getLevel() {
        $result = 0;

        for ($parentPage = $this->parent_page; $parentPage != null; $parentPage = $parentPage->parent_page) {
            $result++;
        }

        return $result;
    }

    protected function getImages() {
        $parentUrl = $this->name === '/'
            ? ''
            : mb_substr($this->name, 0, mb_strlen($this->name) - mb_strlen($this->parent->suffix_));
        $path = $this->parent->file_path . $parentUrl;
        $result = [];

        if (!is_dir($path)) {
            return $result;
        }

        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }

            if (!in_array(strtolower($fileInfo->getExtension()), static::IMAGE_EXTENSIONS)) {
                continue;
            }

            $result[$fileInfo->getFilename()] = Image::new([], $fileInfo->getFilename(), $this);
        }

        ksort($result);
        return $result;
    }
}