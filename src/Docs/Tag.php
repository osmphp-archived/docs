<?php

namespace Osm\Docs\Docs;

use Osm\Core\Object_;

/**
 * @property string $name @required @part
 * @property array $parameters @part Array of allowed parameter names (in key) and their types (in value)
 */
class Tag extends Object_
{
    const INT_PARAMETER = 'int';
}