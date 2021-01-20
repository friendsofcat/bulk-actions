<?php

namespace FriendsOfCat\BulkActions\Facades;

use FriendsOfCat\BulkActions\Dispatcher;
use Illuminate\Support\Facades\Facade;

class BulkAction extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return Dispatcher::class;
    }
}
