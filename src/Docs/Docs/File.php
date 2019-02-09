<?php

namespace Manadev\Docs\Docs;

use Manadev\Docs\Docs\Hints\SettingsHint;
use Manadev\Core\App;
use Manadev\Core\Object_;
use Manadev\Framework\Settings\Settings;
use Michelf\MarkdownExtra;

/**
 * @property string $name @required @part File name of this documentation page
 * @property string $title @required @part
 * @property string $html @required @part
 * @property string $text @required @part
 * @property string $original_text @required @part
 * @property string[] $parent_pages @required @part
 *
 * @property Settings|SettingsHint $settings @required
 * @property string $doc_root @required
 * @property string $base_url @required
 * @property string $public_path @required
 * @property Module $module @required
 * @property Tags|Tag[] $tags @required
 * @property TagRenderer $tag_renderer @required
 * @property TypeConverter $type_converter @required
 * @property UrlGenerator $url_generator @required
 */
class File extends Object_
{
    const H1_PATTERN = "/^#\\s*(?<title>[^#{]+)/u";
    const HEADER_PATTERN = "/^(?<depth>#+)\\s*(?<title>[^#{\\r\\n]+)#*[ \\t]*(?:{(?<attributes>[^}\\r\\n]*)})?\\r?$/mu";
    const IMAGE_LINK_PATTERN = "/!\\[(?<description>[^\\]]*)\\]\\((?<url>[^\\)]+)\\)/u";
    const TAG_PATTERN = "/{{\\s*(?<tag>[^ }]*)(?<args>.*)}}/u";
    const ARG_PATTERN = "/(?<key>[a-z0-9_]+)\\s*=\\s*\"(?<value>[^\"]*)\"/u";
    const ID_PATTERN = "/#(?<id>[^ ]+)/u";
    const CHARS_BEING_REPLACED = [
        // characters listed below when found in SEOified text are replaced by SEO friendly characters from
        // REPLACEMENTS array. For example, ' ' ir replaced with '-'
        ' ', '\\',

        // characters listed below when found in SEOified text are ignored, i.e. not put into generated URL
        '`', '"', '\'', '(', ')', '.', ',', '?',
    ];
    const REPLACEMENTS = ['-', '-'];

    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'title': return $this->getTitle();
            case 'original_text': return file_get_contents($this->name);
            case 'text': return $this->transform($this->original_text);
            case 'html': return MarkdownExtra::defaultTransform($this->text);
            case 'parent_pages': return $this->getParentPages();

            case 'settings': return $m_app->settings;
            case 'doc_root': return $this->settings->doc_root;
            case 'base_url': return $m_app->request->base;
            case 'public_path': return $m_app->path($m_app->public_path);
            case 'module': return $m_app->modules['Manadev_Docs_Docs'];
            case 'tags': return $this->module->tags;
            case 'tag_renderer': return $m_app[TagRenderer::class];
            case 'type_converter': return $m_app[TypeConverter::class];
            case 'url_generator': return $m_app[UrlGenerator::class];
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

    protected function transform($text) {
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

            $imageUrl = $this->base_url . str_replace('\\', '/',
                mb_substr($filename, mb_strlen($this->public_path)));
            return "![{$match['description']}]({$imageUrl})";
        }, $text);
    }

    protected function makeImagePublic($imageUrl) {
        if (!in_array(strtolower(pathinfo($imageUrl, PATHINFO_EXTENSION)), ['png', 'jpg', 'gif'])) {
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
        $result = dirname($this->name);

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
        $result = $this->public_path . '/images';

        $path = mb_substr(dirname($this->name), mb_strlen($this->doc_root));
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

    protected function getParentPages() {
        $result = [];

        $path = $this->name;
        if (basename($path) == 'index.md') {
            $path = dirname($path);
        }
        $path = dirname($path);

        for (; mb_strlen($path) >= mb_strlen($this->doc_root); $path = dirname($path)) {
            $filename = "{$path}/index.md";
            if (!is_file($filename)) {
                continue;
            }

            $file = File::new(['name' => $filename]);
            $result[$this->url_generator->generateUrl($filename)] = $file->title;
        }

        return array_reverse($result);
    }
}