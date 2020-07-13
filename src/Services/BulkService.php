<?php

namespace Bavix\Entry\Services;

use Bavix\Entry\Models\Entry;
use Illuminate\Support\Facades\Redis;

class BulkService
{

    /**
     * @param int $batchSize
     * @param string $key
     * @return \Generator
     */
    public function chunkIterator(int $batchSize, string $key): \Generator
    {
        $start = \strpos($key, $this->prefixKey());
        if ($start !== false) {
            $key = \substr($key, $start);
        }

        do {
            $bulk = Redis::lrange($key, 0, \max($batchSize - 1, 0));
            $count = \count($bulk);
            if ($count) {
                yield $bulk;
                Redis::ltrim($key, $count, -1);
            }
        } while ($count >= $batchSize);
    }

    /**
     * @return array
     */
    public function keys(): array
    {
        return Redis::keys($this->prefixKey() . '*');
    }

    /**
     * @param Entry $entry
     * @return int
     */
    public function insert(Entry $entry): int
    {
        return Redis::rpush($this->writeKey($entry), \json_encode($entry->toArray()));
    }

    /**
     * @param Entry $entry
     * @return string
     */
    public function writeKey(Entry $entry): string
    {
        return $this->prefixKey() . \get_class($entry);
    }

    /**
     * @return string
     */
    protected function prefixKey(): string
    {
        return 'bavixBulkWrite:';
    }

}
