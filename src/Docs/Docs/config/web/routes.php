<?php

use Manadev\Docs\Docs\Controllers\Web;

return [
    'GET /_books/page' => [
        'class' => Web::class,
        'method' => 'show',
        'public' => true,
        'abstract' => true,
    ],
];