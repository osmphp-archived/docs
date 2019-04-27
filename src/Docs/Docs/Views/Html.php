<?php

namespace Manadev\Docs\Docs\Views;

use Manadev\Docs\Docs\Page;
use Manadev\Framework\Views\View;

/**
 * @property Page $page @required
 */
class Html extends View
{
    public $template = 'Manadev_Docs_Docs.html';
}