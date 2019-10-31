<?php

namespace Osm\Docs\Docs\Views;

use Osm\Docs\Docs\Page;
use Osm\Framework\Views\View;
use Osm\Ui\Menus\Views\Menu;

/**
 * @property Page $page @required
 * @property Menu $menu @part
 */
class Breadcrumbs extends View
{
    public $template = 'Osm_Docs_Docs.breadcrumbs';
}