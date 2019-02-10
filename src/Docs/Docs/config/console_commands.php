<?php

use Manadev\Docs\Docs\Commands;

return [
    'show:broken-links' => [
        'description' => m_("Displays broken links in documentation sources"),
        'class' => Commands\ShowBrokenLinks::class,
    ],
];