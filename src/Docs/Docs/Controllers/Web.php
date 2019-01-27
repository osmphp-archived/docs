<?php

namespace Manadev\Docs\Docs\Controllers;

use Manadev\Docs\Docs\File;
use Manadev\Docs\Docs\Views\DocPage;
use Manadev\Framework\Http\Controller;

/**
 * @property File $file @required
 */
class Web extends Controller
{
    public $route = '/show';

    public function show() {
        return m_layout([
            '@include' => 'base',
            '#page' => [
                'title' => $this->file->title,
                'content' => DocPage::new(['file' => $this->file]),
            ],
        ]);
    }
}