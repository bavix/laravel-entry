<?php

namespace Bavix\Entry\Models;

use Bavix\LaravelClickHouse\Database\Eloquent\Model;
use Bavix\Entry\Services\BulkService;

abstract class Entry extends Model
{

    /**
     * @inheritDoc
     */
    public function save(array $options = []): bool
    {
        if (\config('entry.saveViaQueue', false)) {
            return \app(BulkService::class)->insert($this);
        }

        foreach ($this->getAttributes() as $column => $value) {
            if ($value === null) {
                $this->$column = raw('NULL');
            }
        }

        return parent::save($options);
    }

}
