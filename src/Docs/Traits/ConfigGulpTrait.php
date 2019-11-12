<?php

namespace Osm\Docs\Docs\Traits;

use Osm\Core\App;
use Osm\Docs\Docs\Hints\SettingsHint;
use Osm\Framework\Settings\Settings;

trait ConfigGulpTrait
{
    protected function around_getWatchedPatterns(callable $proceed) {
        global $osm_app; /* @var App $osm_app */

        /* @var Settings|SettingsHint $settings */
        $settings = $osm_app->settings;
        $homeDir = $settings->doc_file_path ?? $osm_app->path('data/book');

        $result = $proceed();
        if (mb_substr(realpath($homeDir), realpath('data')) === 0) {
            return $result;
        }

        return array_merge($result, ["{$homeDir}/**/*.*"]);
    }

}