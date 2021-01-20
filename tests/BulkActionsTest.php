<?php

namespace FriendsOfCat\BulkActions\Tests;

use FriendsOfCat\BulkActions\BulkActionsServiceProvider;
use FriendsOfCat\BulkActions\Dispatcher;
use FriendsOfCat\BulkActions\Tests\Stubs\TestAction;
use FriendsOfCat\BulkActions\Tests\Stubs\TestActionNoExecuteMethod;
use FriendsOfCat\BulkActions\Tests\Stubs\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Orchestra\Testbench\TestCase;
use RuntimeException;

class BulkActionsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var Dispatcher */
    protected $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        $this->dispatcher = $this->app->make(Dispatcher::class);

        Queue::fake();
    }

    public function testCanDispatchSingleAction(): void
    {
        $models = new Collection([
            $this->createUser(),
        ]);

        $this->dispatcher->dispatch(TestAction::class, $models);

        Queue::assertPushed(TestAction::class);
    }

    public function testCanDispatchMultipleActions(): void
    {
        $models = new Collection([
            $this->createUser(),
            $this->createUser(),
        ]);

        $this->dispatcher->dispatch(TestAction::class, $models);

        Queue::assertPushed(TestAction::class);
        Queue::assertPushed(TestAction::class);
    }

    public function testActionClassRequiresExecuteMethod(): void
    {
        $models = new Collection([
            $this->createUser(),
        ]);

        $this->expectException(RuntimeException::class);

        $this->dispatcher->dispatch(TestActionNoExecuteMethod::class, $models);

        Queue::assertNothingPushed();
    }

    protected function getPackageProviders($app)
    {
        return [
            BulkActionsServiceProvider::class,
        ];
    }

    protected function createUser(): User
    {
        return User::create([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
        ]);
    }
}
