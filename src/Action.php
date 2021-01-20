<?php

namespace FriendsOfCat\BulkActions;

use FriendsOfCat\BulkActions\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

abstract class Action implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The bulk action job instance.
     *
     * @var \FriendsOfCat\BulkActions\Models\Job
     */
    protected $bulkActionJob;

    /**
     * The model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Create a new job instance.
     *
     * @param  \FriendsOfCat\BulkActions\Models\Job  $bulkActionJob
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function __construct(Job $bulkActionJob, Model $model)
    {
        $this->bulkActionJob = $bulkActionJob;
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    final public function handle(Container $container): void
    {
        $this->bulkActionJob->markAsRunning();

        $container->call([$this, 'execute']);

        $this->bulkActionJob->markAsComplete();
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    final public function failed(Throwable $e): void
    {
        $this->bulkActionJob->markAsFailed($e);
    }
}
