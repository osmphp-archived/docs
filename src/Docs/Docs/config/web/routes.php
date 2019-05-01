<?php

use Manadev\Docs\Docs\Controllers\Web;

return [
    'GET /_books/page' => [
        'class' => Web::class,
        'method' => 'bookPage',
        'public' => true,
        'abstract' => true,
    ],
    'GET /_books/image' => [
        'class' => Web::class,
        'method' => 'image',
        'public' => true,
        'abstract' => true,
    ],
];