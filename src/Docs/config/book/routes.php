<?php

use Osm\Docs\Docs\Controllers\BookController;

return [
    'GET /_books/page' => [
        'class' => BookController::class,
        'method' => 'bookPage',
        'public' => true,
        'abstract' => true,
    ],
    'GET /_books/image' => [
        'class' => BookController::class,
        'method' => 'image',
        'public' => true,
        'abstract' => true,
    ],
];