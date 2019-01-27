<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\App;
use Manadev\Core\Modules\BaseModule;

/**
 * @property Tags|Tag[] $tags @required
 */
class Module extends BaseModule
{
    protected function default($property) {
        global $m_app; /* @var App $m_app */

        switch ($property) {
            case 'tags': return $m_app->cache->remember('doc_tags', function($data) {
                return Tags::new($data);
            });
        }
        return parent::default($property);
    }
}