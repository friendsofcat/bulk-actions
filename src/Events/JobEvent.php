<?php

namespace FriendsOfCat\BulkActions\Events;

use FriendsOfCat\BulkActions\Models\Job;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class JobEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The bulk action job model instance.
     *
     * @var \FriendsOfCat\BulkActions\Models\Job
     */
    public $job;

    /**
     * Create a new event instance.
     *
     * @param  \FriendsOfCat\BulkActions\Models\Job  $job
     * @return void
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel("bulk-actions.batch.{$this->job->batch_id}");
    }
}
