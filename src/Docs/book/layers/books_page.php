<?php

use Osm\Docs\Docs\Views\Breadcrumbs;
use Osm\Docs\Docs\Views\Html;
use Osm\Framework\Views\Views\Container;
use Osm\Ui\MenuBars\Views\MenuBar;

return [
    '@include' => ['page'],
    '#page' => [
        'modifier' => '-books-page',
        'content' => Container::new([
            'id' => 'content',
            'modifier' => 'page-section',
            'views' => [
                'breadcrumbs' => Breadcrumbs::new([
                    'id' => 'breadcrumbs',
                    'menu' => MenuBar::new(['items' => []]),
                ]),
                'main' => Html::new(['id' => 'html']),
            ],
        ]),
    ],
];