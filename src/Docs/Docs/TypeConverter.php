<?php

namespace Osm\Docs\Docs;

use Osm\Core\Exceptions\NotSupported;
use Osm\Core\Object_;

class TypeConverter extends Object_
{
    /**
     * @see \Osm\Docs\Docs\Tag::$parameters @handler
     *
     * @param $type
     * @param $value
     * @return int
     */
    public function convert($type, $value) {
        switch ($type) {
            case Tag::INT_PARAMETER: return intval($value);
            default:
                throw new NotSupported(osm_t("Tag parameter type ':type' not supported", ['type' => $type]));
        }
    }
}