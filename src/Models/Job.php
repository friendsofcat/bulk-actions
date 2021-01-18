<?php

namespace FriendsOfCat\BulkActions\Models;

use FriendsOfCat\BulkActions\Events\JobCompleted;
use FriendsOfCat\BulkActions\Events\JobFailed;
use FriendsOfCat\BulkActions\Events\JobStarted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Throwable;

class Job extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'bulk_action_jobs';

    /**
     * {@inheritDoc}
     */
    protected $attributes = [
        'status' => Status::QUEUED,
    ];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'started_at' => 'datetime',
        'failed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the batch that owns the job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the related model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark the job as running.
     *
     * @return void
     */
    public function markAsRunning(): void
    {
        $this->forceFill([
            'status' => Status::RUNNING,
            'started_at' => $this->freshTimestamp(),
        ])->save();

        JobStarted::dispatch($this);
    }

    /**
     * Mark the job as complete.
     *
     * @return void
     */
    public function markAsComplete(): void
    {
        $this->forceFill([
            'status' => Status::COMPLETE,
            'completed_at' => $this->freshTimestamp(),
        ])->save();

        JobCompleted::dispatch($this);
    }

    /**
     * Mark the job as failed.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function markAsFailed(Throwable $e): void
    {
        $this->forceFill([
            'status' => Status::FAILED,
            'error' => (string) $e,
            'failed_at' => $this->freshTimestamp(),
        ])->save();

        JobFailed::dispatch($this);
    }
}
