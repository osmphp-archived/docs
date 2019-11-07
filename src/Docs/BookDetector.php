<?php

namespace Osm\Docs\Docs;

use Osm\Core\App;
use Osm\Core\Object_;
use Osm\Docs\Docs\Hints\SettingsHint;
use Osm\Framework\Http\Request;
use Osm\Framework\Http\Url;
use Osm\Framework\Settings\Settings;

/**
 * @property Settings|SettingsHint $settings @required
 * @property string $file_path
 * @property string $url_path
 * @property Request $request @required
 */
class BookDetector extends Object_
{
    protected function default($property) {
        global $osm_app; /* @var App $osm_app */

        switch ($property) {
            case 'settings': return $osm_app->settings;
            case 'file_path': return $this->settings->doc_file_path
                ?? $osm_app->path('data/book');
            case 'url_path': return $this->settings->doc_url_path;
            case 'request': return $osm_app->request;
        }
        return parent::default($property);
    }

    /**
     * @return Book
     */
    public function detectBook() {
        if ($this->url_path === null) {
            return null;
        }

        if (!is_file("{$this->file_path}/index.md")) {
            return null;
        }

        if ($this->url_path &&
            mb_strpos($this->request->route, $this->url_path) !== 0)
        {
            return null;
        }

        return Book::new([
            'file_path' => $this->file_path,
            'suffix' => 'html',
            'url' => Url::new([
                'area' => 'book',
                'route_base_url' => $this->url_path,
            ]),
        ]);
    }
}