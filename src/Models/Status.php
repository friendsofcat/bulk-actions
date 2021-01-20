<?php

namespace FriendsOfCat\BulkActions\Models;

class Status
{
    /**
     * The queued bulk action status.
     *
     * @var string
     */
    public const QUEUED = 'queued';

    /**
     * The running bulk action status.
     *
     * @var string
     */
    public const RUNNING = 'running';

    /**
     * The failed bulk action status.
     *
     * @var string
     */
    public const FAILED = 'failed';

    /**
     * The complete bulk action status.
     *
     * @var string
     */
    public const COMPLETE = 'complete';
}
