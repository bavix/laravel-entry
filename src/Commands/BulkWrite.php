<?php

namespace Bavix\Entry\Commands;

use Bavix\Entry\Services\BulkService;
use Bavix\Entry\Jobs\BulkWriter;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class BulkWrite extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'entry:bulk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull out from redis and throws in the queue for recording';

    /**
     * @return void
     * @throws
     */
    public function handle(): void
    {
        $lock = Cache::lock(__CLASS__, 120);
        try {
            $lock->block(1);
            // Lock acquired after waiting maximum of second...
            $batchSize = \config('entry.batchSize', 10000);
            $keys = app(BulkService::class)->keys();
            foreach ($keys as $key) {
                [$bulkName, $class] = \explode(':', $key, 2);
                $chunkIterator = app(BulkService::class)
                    ->chunkIterator($batchSize, $key);

                foreach ($chunkIterator as $bulkData) {
                    foreach ($bulkData as $itemKey => $itemValue) {
                        $bulkData[$itemKey] = \json_decode($itemValue, true);
                    }

                    $queueName = \config('entry.queueName', 'default');
                    $job = new BulkWriter(new $class, $bulkData);
                    $job->onQueue($queueName);
                    \dispatch($job);
                }
            }
        } catch (LockTimeoutException $timeoutException) {
            // Unable to acquire lock...
        } finally {
            optional($lock)->release();
        }
    }

}
