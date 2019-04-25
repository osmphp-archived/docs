<?php

namespace Manadev\Docs\Docs\Views;

use Manadev\Docs\Docs\File;
use Manadev\Framework\Views\View;
use Manadev\Ui\Menus\Views\Menu;

/**
 * @property File $file @required
 * @property Menu $menu @part
 */
class Breadcrumbs extends View
{
    public $template = 'Manadev_Docs_Docs.breadcrumbs';
}