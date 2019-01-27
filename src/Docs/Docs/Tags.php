<?php

namespace Manadev\Docs\Docs;

use Manadev\Framework\Data\CollectionRegistry;

class Tags extends CollectionRegistry
{
    public $class_ = Tag::class;
    public $config = 'doc_tags';
    public $not_found_message = "Tag ':name' not found";
}