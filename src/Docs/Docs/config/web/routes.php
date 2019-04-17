<?php

use Manadev\Docs\Docs\Controllers\Web;
use Manadev\Framework\Http\Parameters;

return [
    'GET /__books/pages/' => [
        'class' => Web::class,
        'method' => 'show',
        'public' => true,
        'abstract' => true,
    ],
];