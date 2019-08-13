<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\App;
use Manadev\Core\Object_;
use Manadev\Docs\Docs\Hints\SettingsHint;
use Manadev\Framework\Settings\Settings;

/**
 * @property Settings|SettingsHint $settings @required
 */
class BookDetector extends Object_
{
    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'settings': return $m_app->settings;
        }
        return parent::default($property);
    }

    /**
     * @return Book
     */
    public function detectBook() {
        if (!($path = $this->settings->doc_root)) {
            return null;
        }

        if (!is_dir($path)) {
            return null;
        }

        return Book::new(['file_path' => $path, 'url_path' => '', 'suffix' => 'html']);
    }
}