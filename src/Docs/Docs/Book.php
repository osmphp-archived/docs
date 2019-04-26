<?php

namespace Manadev\Docs\Docs;

use Manadev\Core\Object_;

/**
 * @property string $file_path @required @part
 * @property string $url_path @required @part
 * @property string $suffix @part Typical values: null, '/', 'html', ''
 * @property string $suffix_ @required
 *
 * @see \Manadev\DocHost\Books\Module:
 *      @property int $id @required @part
 *      @property int $customer @required @part
 */
class Book extends Object_
{
    protected function default($property) {
        switch ($property) {
            case 'suffix_': return $this->getSuffix();
        }

        return parent::default($property);
    }

    protected function getSuffix() {
        if (!$this->suffix) {
            return '';
        }

        if ($this->suffix == '/' || mb_strpos($this->suffix, '.') === 0) {
            return $this->suffix;
        }

        return '.' . $this->suffix;
    }
}