<?php

namespace FriendsOfCat\BulkActions;

use FriendsOfCat\BulkActions\Models\Batch;
use FriendsOfCat\BulkActions\Models\Job;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class Dispatcher
{
    /**
     * The auth guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The bus dispatcher implementation.
     *
     * @var \Illuminate\Contracts\Bus\Dispatcher
     */
    protected $queue;

    /**
     * The database connection implementation.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $db;

    /**
     * Create a new bulk action dispatcher instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $auth
     * @param  \Illuminate\Contracts\Bus\Dispatcher  $queue
     * @return void
     */
    public function __construct(Guard $auth, BusDispatcher $queue, ConnectionInterface $db)
    {
        $this->auth = $auth;
        $this->queue = $queue;
        $this->db = $db;
    }

    /**
     * Dispatch the named action with the given models.
     *
     * @param  string  $action
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     *
     * @return \FriendsOfCat\BulkActions\Models\Batch
     */
    public function dispatch(string $action, Collection $models): Batch
    {
        $this->ensureActionIsValid($action);

        $batch = $this->db->transaction(function () use ($models) {
            return $this->createBatchJobs($this->createBatch(), $models);
        });

        $this->dispatchQueueJobs($batch, $action);

        return $batch;
    }

    /**
     * Ensure that the given action is a valid class, and has a method named execute.
     *
     * @param  string  $action
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function ensureActionIsValid(string $action): void
    {
        if (! class_exists($action)) {
            throw new InvalidArgumentException(sprintf('Class [%s] does not exist'));
        }

        if (! method_exists($action, 'execute')) {
            throw new RuntimeException(sprintf('Class [%s] requires a method named execute', $action));
        }
    }

    /**
     * Create a new batch instance.
     *
     * @return \FriendsOfCat\BulkActions\Models\Batch
     */
    protected function createBatch(): Batch
    {
        /** @var \FriendsOfCat\BulkActions\Models\Batch $batch */
        $batch = Batch::make();

        if ($this->auth->check()) {
            $batch->user()->associate($this->auth->user());
        }

        $batch->save();

        return $batch;
    }

    /**
     * Create a batch job for each of the given models.
     *
     * @param  \FriendsOfCat\BulkActions\Models\Batch  $batch
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return \FriendsOfCat\BulkActions\Models\Batch
     */
    protected function createBatchJobs(Batch $batch, Collection $models): Batch
    {
        Log::debug(__('Creating :count job(s) for batch :batch', [
            'count' => $models->count(),
            'batch' => $batch->getKey(),
        ]));

        $models->each(function (Model $model) use ($batch) {
            $job = $batch->jobs()->make();

            $job->model()->associate($model)->save();
        });

        return $batch;
    }

    /**
     * Dispatch a queue job for each bulk action job.
     *
     * @param  \FriendsOfCat\BulkActions\Models\Batch  $batch
     * @param  string  $action
     * @return void
     */
    protected function dispatchQueueJobs(Batch $batch, string $action): void
    {
        $batch->fresh('jobs.model')->jobs->each(function (Job $job) use ($action) {
            $this->queue->dispatch(new $action($job, $job->model));
        });
    }
}
