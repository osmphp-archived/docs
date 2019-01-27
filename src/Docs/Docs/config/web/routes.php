<?php

use Manadev\Docs\Docs\Controllers\Web;
use Manadev\Framework\Http\Parameters;

return [
    'GET /show' => [
        'class' => Web::class,
        'method' => 'show',
        'public' => true,
        'seo' => true,
        'parameters' => [
            'page' => [
                'class' => Parameters\String_::class,
                'required' => true,
            ],
        ],
    ],
];