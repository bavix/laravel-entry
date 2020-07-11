<?php

namespace Bavix\Entry\Jobs;

use Bavix\Entry\Models\Entry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class BulkWriter implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Entry
     */
    protected $entry;

    /**
     * @var array[]
     */
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param Entry $entry
     * @param array[] $data
     * @return void
     */
    public function __construct(Entry $entry, ?array $data = null)
    {
        if ($data === null) {
            $data = $entry->toArray();
        }

        $this->entry = $entry;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->data) {
            \array_walk_recursive($this->data, static function (&$value) {
                $value = $value ?? raw('NULL');
            });

            $this->entry::insert($this->data);
        }
    }

}
