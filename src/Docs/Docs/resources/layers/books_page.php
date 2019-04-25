<?php

use Manadev\Docs\Docs\Views\Breadcrumbs;
use Manadev\Docs\Docs\Views\Html;
use Manadev\Framework\Views\Views\Container;
use Manadev\Ui\MenuBars\Views\MenuBar;

return [
    '@include' => ['base'],
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