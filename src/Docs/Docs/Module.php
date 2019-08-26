<?php

namespace Osm\Docs\Docs;

use Osm\Core\App;
use Osm\Core\Modules\BaseModule;
use Osm\Framework\Http\Advices\DetectRoute;

/**
 * @property Tags|Tag[] $tags @required
 * @property Book $book @required
 * @property Page $page @required
 * @property string $image @required
 */
class Module extends BaseModule
{
    public $hard_dependencies = [
        'Osm_Ui_Aba',
    ];

    public $traits = [
        DetectRoute::class => Traits\DetectRouteTrait::class,
    ];

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