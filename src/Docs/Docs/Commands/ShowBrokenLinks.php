<?php

namespace Manadev\Docs\Docs\Commands;

use Manadev\Core\App;
use Manadev\Docs\Docs\Page;
use Manadev\Docs\Docs\FileFinder;
use Manadev\Docs\Docs\UrlGenerator;
use Manadev\Framework\Console\Command;

/**
 * @property string $doc_root @required
 * @property FileFinder $file_finder @required
 * @property UrlGenerator $url_generator @required
 *
 * @property string $filename @temp
 * @property bool $filename_rendered @temp
 * @property string $line_no @temp
 * @property string $url_path @temp
 * @property string $url_fragment @temp
 * @property string $referenced_filename @temp
 */
class ShowBrokenLinks extends Command
{
    public function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'doc_root': return $m_app->settings->doc_root;
            case 'file_finder': return $m_app[FileFinder::class];
            case 'url_generator': return $m_app[UrlGenerator::class];
        }
        return parent::default($property);
    }

    public function run() {
        $this->checkDirectory('');
    }

    protected function checkDirectory($path) {
        foreach (new \DirectoryIterator($this->doc_root . ( $path ? '/' . $path : '')) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $this->checkDirectory(($path ? $path . '/' : '') . $fileInfo->getFilename());
                continue;
            }

            if (strtolower(pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION)) != 'md') {
                continue;
            }

            $this->checkFile($fileInfo->getPathname());
        }
    }

    protected function checkFile($filename) {
        $this->filename = $filename;
        $this->filename_rendered = false;
        foreach (explode("\n", file_get_contents($filename)) as $lineNo => $line) {
            $this->line_no = $lineNo;
            if (preg_match_all(Page::LINK_PATTERN, $line, $match)) {
                foreach ($match['url'] as $url) {
                    $this->checkUrl($url);
                }
            }
        }

    }

    protected function checkUrl($url) {
        if (starts_with($url, ['http://', 'https://', '///'])) {
            // don't check external links
            return;
        }

        if ($url == '#') {
            $this->renderFilename();
            $this->output->writeln(m_("  line :line: placeholder '#'", ['line' => $this->line_no]));
            return;
        }

        if (($pos = mb_strpos($url, '#')) !== false) {
            $this->url_path = mb_substr($url, 0, $pos);
            $this->url_fragment = mb_substr($url, $pos + 1);
        }
        else {
            $this->url_path = $url;
            $this->url_fragment = '';
        }

        if (!$this->checkUrlPath()) {
            return;
        }
        $this->checkUrlFragment();
    }

    protected function renderFilename() {
        if (!$this->filename_rendered) {
            $this->filename_rendered = true;
            $this->output->writeln(mb_substr($this->filename, mb_strlen($this->doc_root) + 1));
        }
    }

    protected function checkUrlPath() {
        if (!$this->url_path) {
            $this->referenced_filename = $this->filename;
            return true;
        }

        $currentUrl = $this->url_generator->generateRelativeUrl($this->filename);
        $currentUrl = mb_substr($currentUrl, 0, mb_strrpos($currentUrl, '/') + 1);
        $url = $this->resolveUrl($currentUrl . $this->url_path);

        if (!($this->referenced_filename = $this->file_finder->findFile($url))) {
            $this->renderFilename();
            $this->output->writeln(m_("  line :line: ':url' not found", [
                'line' => $this->line_no,
                'url' => $url,
            ]));

            return false;
        }

        return true;
    }

    protected function resolveUrl($url) {
        $parts = explode('/', $url);
        for ($i = 0; ; $i++) {
            if ($i >= count($parts)) {
                break;
            }

            if ($parts[$i] == '..') {
                array_splice($parts, $i - 1, 2);
                $i -= 2;
            }
        }
        return implode('/', $parts);
    }

    protected function checkUrlFragment() {
    }

}