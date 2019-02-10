<?php

namespace Manadev\Docs\Docs;

use Manadev\Docs\Docs\Hints\SettingsHint;
use Manadev\Core\App;
use Manadev\Core\Object_;
use Manadev\Framework\Settings\Settings;

/**
 * @property Settings|SettingsHint $settings @required
 * @property string $doc_root @required
 * @property string $base_url @required
 */
class UrlGenerator extends Object_
{
     protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'settings': return $m_app->settings;
            case 'doc_root': return $this->settings->doc_root;
            case 'base_url': return $m_app->request->base;
        }
        return parent::default($property);
    }

    public function generateUrl($filename) {
        return  $this->base_url . $this->generateRelativeUrl($filename);
    }

    public function generateRelativeUrl($filename) {
        $filename = mb_substr($filename, mb_strlen($this->doc_root) + 1);
        $result = '';

        foreach (explode('/', str_replace("\\", '/', $filename)) as $part) {
            $result .= '/';
            if ($part == 'index.md') {
                continue;
            }

            $result .= preg_replace("/(\\d+-)|(\\.md)/u", '', $part);
        }
        return $result;
    }
}