<?php

namespace Osm\Docs\Docs;

use Osm\Core\App;
use Osm\Core\Object_;
use Osm\Framework\Http\Request;
use Michelf\MarkdownExtra;
use Osm\Framework\Http\Url;

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
 * Properties applicable to PAGE and PLACEHOLDER types, inferred from `name` property:
 *
 * @property string $filename @required @part File name of this book page
 * @property string $title @required @part
 * @property string $original_title @required @part
 * @property string $html @required @part
 * @property string $text @required @part
 * @property string $original_text @required @part
 * @property int $level @required @part
 * @property string $sort_order
 * @property string $url @required
 *
 * Properties applicable to PAGE and PLACEHOLDER types, collected from file system:
 *
 * @property Page $parent_page
 * @property Page[] $parent_pages @required
 * @property Page[] $sibling_pages @required
 * @property Page[] $child_pages @required
 * @property Image[] $images @required
 *
 * Properties read from property section in the end of the document
 * @property object $properties @part
 * @property string $child_page_direction @required @part
 *
 * Dependencies:
 *
 * @property Module $module @required
 * @property Tags|Tag[] $tags @required
 * @property TagRenderer $tag_renderer @required
 * @property TypeConverter $type_converter @required
 * @property Url $url_ @required
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
    const ALTERNATE_HEADER_PATTERN = "/\\n(?<title>[^{\\r\\n]+)(?:{(?<attributes>[^}\\r\\n]*)})?\\r?\\n--/mu";
    const IMAGE_LINK_PATTERN = "/!\\[(?<description>[^\\]]*)\\]\\((?<url>[^\\)]+)\\)/u";
    const TAG_PATTERN = "/(?<whitespace> {4})?(?<opening_backtick>`)?{{\\s*(?<tag>[^ }]*)(?<args>.*)}}(?<closing_backtick>`)?/u";
    const ARG_PATTERN = "/(?<key>[a-z0-9_]+)\\s*=\\s*\"(?<value>[^\"]*)\"/u";
    const ID_PATTERN = "/#(?<id>[^ ]+)/u";
    const LINK_PATTERN = "/\\[(?<title>[^\\]]+)\\]\\((?<url>[^\\)]+)\\)/u";

    const CHARS_BEING_REPLACED = [
        // characters listed below when found in SEOified text are replaced by SEO friendly characters from
        // REPLACEMENTS array. For example, ' ' ir replaced with '-'
        ' ', '\\', '/',

        // characters listed below when found in SEOified text are ignored, i.e. not put into generated URL
        '`', '"', '\'', '(', ')', '.', ',', '?', '!', '+', '@', ':', '&', '>',
        '<',
    ];
    const REPLACEMENTS = ['-', '-', '-'];

    const IMAGE_EXTENSIONS = ['png', 'jpg', 'gif'];

    const ASC = 'asc';
    const DESC = 'desc';

    protected function default($property) {
        global $osm_app; /* @var App $osm_app */

        switch ($property) {
            case 'original_title': return $this->getOriginalTitle();
            case 'title': return $this->getTitle();
            case 'original_text': return $this->type == static::PLACEHOLDER
                ? $this->parent->getPlaceholderText($this->name)
                : file_get_contents($this->filename);
            case 'text': return $this->transformText($this->original_text);
            case 'html': return $this->transformHtml(MarkdownExtra::defaultTransform($this->text));
            case 'level': return $this->getLevel();
            case 'sort_order': return $this->getSortOrder();
            case 'url': return $this->parent->getPageUrl($this->name);
            case 'redirect_to_url': return $this->parent->getPageUrl($this->redirect_to);

            case 'properties': return $this->getProperties();
            case 'child_page_direction': return $this->getChildPageDirection();

            case 'parent_page': return $this->getParentPage();
            case 'parent_pages': return $this->getParentPages();
            case 'sibling_pages': return $this->getSiblingPages();
            case 'child_pages': return $this->getChildPages();
            case 'images': return $this->getImages();

            case 'module': return $osm_app->modules['Osm_Docs_Docs'];
            case 'tags': return $this->module->tags;
            case 'tag_renderer': return $osm_app[TagRenderer::class];
            case 'type_converter': return $osm_app[TypeConverter::class];
            case 'book': return $this->module->book;
            case 'request': return $osm_app->request;
            case 'url_': return $osm_app->url;
        }
        return parent::default($property);
    }

    protected function getOriginalTitle() {
        foreach (explode("\n", $this->original_text) as $line) {
            if (preg_match(static::H1_PATTERN, $line, $match)) {
                return trim($match['title']);
            }
        }

        return '';
    }

    protected function getTitle() {
        return str_replace('`', '', $this->original_title);
    }

    protected function transformText($text) {
        $text = $this->removePropertySection($text);
        $text = $this->addTransientQueryParametersToLinks($text);
        $text = $this->assignHeadingIds($text);
        $text = $this->processTags($text);
        return $text;
    }

    protected function removePropertySection($text) {
        return $this->properties ? mb_substr($text, 0, mb_strrpos($text, "\n---")) : $text;
    }

    protected function addTransientQueryParametersToLinks($text) {
        return preg_replace_callback(static::LINK_PATTERN, function($match) use ($text) {
            if (preg_match('#^https?://#', $match['url'])) {
                return $match[0];
            }

            return "[{$match['title']}]({$match['url']}{$this->url_->
                generateQueryString($this->request->query)})";
        }, $text);
    }

    protected function processTags($text) {
        return preg_replace_callback(static::TAG_PATTERN, function($match) use ($text) {
            // don't expand tags in code block
            if (!empty($match['whitespace'])) {
                return $match[0];
            }

            // don't expand tags in inline code
            if (!empty($match['opening_backtick']) && !empty($match['closing_backtick'])) {
                return $match[0];
            }

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

        $text = preg_replace_callback(static::HEADER_PATTERN, function($match) use (&$ids){
            $attributes = $match['attributes'] ?? '';
            if (mb_strpos($attributes, '#') !== false) {
                return $match[0];
            }

            $id = $this->generateId($match['title'], $ids);

            return "{$match['depth']} {$match['title']} {$match['depth']} {#{$id} {$attributes}}";
        }, $text);

        $text = preg_replace_callback(static::ALTERNATE_HEADER_PATTERN, function($match) use (&$ids){
            $attributes = $match['attributes'] ?? '';
            if (mb_strpos($attributes, '#') !== false) {
                return $match[0];
            }

            $id = $this->generateId($match['title'], $ids);

            return "{$match['title']} {#{$id} {$attributes}}\r\n--";
        }, $text);

        return $text;
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

        $result = ['/' => $this->parent->getPageByName('/')];
        $url = rtrim($this->name, '/');
        for ($pos = mb_strpos($url, '/', 1); $pos !== false; $pos = mb_strpos($url, '/', $pos + 1)) {
            $result[] = $this->parent->getPageByName(mb_substr($url, 0, $pos));
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
        $parentUrl = rtrim($this->name, '/');
        $path = $this->parent->file_path . $parentUrl;
        $result = [];

        if (!is_dir($path)) {
            return $result;
        }

        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if (starts_with($fileInfo->getFilename(), '.')) {
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

        return $this->parent->sortPages($result, $this->child_page_direction);
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
        $parentUrl = $this->name === '/' ? '' : $this->name;
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

            $result[$fileInfo->getFilename()] = Image::new([
                'url' => "{$parentUrl}/{$fileInfo->getFilename()}",
            ], $fileInfo->getFilename(), $this);
        }

        ksort($result);
        return $result;
    }

    protected function getSortOrder() {
        if ($this->type != static::PAGE) {
            return null;
        }

        if (!preg_match("/(?:(?<sort_order>\\d+)-)?.*\\.md/u", basename($this->filename), $match)) {
            return null;
        }

        return $match['sort_order'] ?? null;
    }


    protected function getProperties() {
        $text = $this->original_text;

        if (($pos = mb_strrpos($text, "\n---")) === false) {
            return null;
        }

        if (($pos = mb_strpos($text, "\n", $pos + mb_strlen("\n---"))) === false) {
            return null;
        }

        return json_decode(mb_substr($text, $pos));
    }

    protected function getChildPageDirection() {
        $result = strtolower($this->properties->child_page_direction
            ?? $this->parent_page->child_page_direction
            ?? static::ASC);

        return $result != static::DESC ? static::ASC : static::DESC;
    }
}