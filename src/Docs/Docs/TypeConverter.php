<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\Exceptions\NotSupported;
use Manadev\Core\Object_;

class TypeConverter extends Object_
{
    /**
     * @see \Manadev\Docs\Docs\Tag::$parameters @handler
     *
     * @param $type
     * @param $value
     * @return int
     */
    public function convert($type, $value) {
        switch ($type) {
            case Tag::INT_PARAMETER: return intval($value);
            default:
                throw new NotSupported(m_("Tag parameter type ':type' not supported", ['type' => $type]));
        }
    }
}