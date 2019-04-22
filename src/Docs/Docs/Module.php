<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\App;
use Manadev\Core\Modules\BaseModule;
use Manadev\Framework\Http\Advices\DetectRoute;

/**
 * @property Tags|Tag[] $tags @required
 * @property Book $book @required
 * @property File $file @required
 */
class Module extends BaseModule
{
    public $hard_dependencies = [
        'Manadev_Ui_Aba',
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