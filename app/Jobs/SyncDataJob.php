<?php

namespace App\Jobs;

use App\Lib\Cache\Caching;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $path;

    protected $model_name;

    protected $relation_names;

    public function __construct(string $path, string $model_name, array $relation_names)
    {
        $this->path = $path;
        $this->model_name = $model_name;
        $this->relation_names = $relation_names;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $redis = new Caching();
            $redis->deleteCache($this->path, $this->model_name, $this->relation_names);
        } catch (\Exception $exception) {
            //TODO: notification tele or job_failed
            print_r($exception);
        }
    }
}
