<?php

namespace Osm\Docs\Docs;

use App\Books\Hints\BookHint;
use App\Books\Sheets\Books;
use Osm\Core\App;
use Osm\Docs\Docs\Hints\SettingsHint;
use Osm\Framework\Cache\Cache;
use Osm\Framework\Gulp\FileWatcher as BaseFileWatcher;
use Osm\Framework\Settings\Settings;

/**
 * @property Cache $cache @required
 * @property Settings|SettingsHint $settings @required
 * @property string $file_path
 */
class FileWatcher extends BaseFileWatcher
{
    protected function default($property) {
        global $osm_app; /* @var App $osm_app */

        switch ($property) {
            case 'cache': return $osm_app->cache;
            case 'settings': return $osm_app->settings;
            case 'file_path': return $this->settings->doc_file_path
                ?? $osm_app->path('data/book');
        }
        return parent::default($property);
    }

    public function handle($paths) {
        if ($this->isBookChanged($paths)) {
            $this->cache->flushTag("book");
        }
    }

    protected function isBookChanged($paths) {
        if (!($homeDir = realpath($this->file_path))) {
            // if book directory doesn't exist, we can't say that
            // the book is changed
            return false;
        }

        foreach ($paths as $path) {
            if (mb_strpos(realpath($path), $homeDir) === 0) {
                return true;
            }
        }

        return false;
    }
}