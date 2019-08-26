<?php

namespace Osm\Docs\Docs;

use Osm\Framework\Data\CollectionRegistry;

class Tags extends CollectionRegistry
{
    public $class_ = Tag::class;
    public $config = 'doc_tags';
    public $not_found_message = "Tag ':name' not found";
}