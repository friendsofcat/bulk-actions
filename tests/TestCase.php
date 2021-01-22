<?php

namespace FriendsOfCat\BulkActions\Tests;

use FriendsOfCat\BulkActions\BulkActionsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            BulkActionsServiceProvider::class,
        ];
    }
}
