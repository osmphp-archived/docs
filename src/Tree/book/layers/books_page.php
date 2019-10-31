<?php

use Osm\Docs\Tree\Views\Items;
use Osm\Docs\Tree\Views\Tree;
use Osm\Framework\Views\View;
use Osm\Framework\Views\Views\Container;
use Osm\Ui\Menus\Items\Type;

return [
    '@assign #content.views[main]' => Container::new([
        'modifier' => 'tree-container',
        'views' => [
            'tree' => Tree::new([
                'id' => 'tree',
                'renderer' => Items::new(),
                'contents_button' => 'breadcrumbs__menu__contents',
                'drawer' => '_body_end_book-page-tree-drawer',
                'sort_order' => 20,
            ]),
        ],
    ]),
    '@move #html' => '#content.views[main].views[main]',
    '#html' => [
        'sort_order' => 10,
    ],
    '#breadcrumbs.menu' => [
        'items' => [
            'contents' => [
                'type' => Type::COMMAND,
                'title' => osm_t("Contents"),
                'modifier' => '-filled',
                'sort_order' => 5,
            ],
        ],
    ],
    '#page' => [
        'body_end' => [
            'book-page-tree-drawer' => View::new(['template' => 'Osm_Docs_Tree.drawer']),
        ],
    ],
];