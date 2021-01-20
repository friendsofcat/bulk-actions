# Bulk Actions
A package that adds [Nova][1]-inspired “bulk“ actions to a Laravel application.

## Installation
```
composer require friendsofcat/bulk-actions
```

## Configuration
The package has a configuration file, which you can publish using the following command:

```
php artisan vendor:publish --tag=bulk-actions-config
```

When a bulk action is ran, it is associated with the currently authenticated user. The `Batch` model contains a foreign key pointing to the user. You may configure the <abbr title="Fully Qualified Name">FQN</abbr> of your user class:

```php
return [
    'user_model' => 'App\Domain\Users\Models\User',
];
```

By default, the package will look for `App\Models\User` and `App\User`, in that order. So if the FQN of your user model is one of these, you do not need to publish the configuration file.

## Usage
The package integrates with [Laravel’s queues][2].

### Defining Actions
An “action” is a piece of code you wish to run against one or more models. You define actions by extending the package’s `Action` class. Your class should include an `execute` method. The class will have a `$model` property.

```php
class TestAction extends Action
{
    public function execute()
    {
        Log::debug('Test action executed for model', [
            'model_type' => get_class($this->model),
            'model_id' => $this->model->getKey(),
        ]);
    }
}
```

The `execute` method is resolved by the service container, so you may type-hint any dependencies registered in the container that you need to perform the action on the model:

```php
class GeocodeAddress extends Action
{
    public function execute(Geocoder $geocoder)
    {
        if ($this->model instanceof Geocodable) {
            $geocoder->geocode($this->model->getAddress());
        }
    }
}
```

### Dispatching actions
Actions are dispatched using the `FriendsOfCat\BulkActions\Dispatcher` class. You can type-hint the class in your own classes and then call its `dispatch` method. The method takes two parameters:

1. The name of the action class to dispatch as a string.
2. A collection of Eloquent models to execute the action against.

**Note:** The collection must be an instance of `Illuminate\Database\Eloquent\Collection`.

```php
use App\Actions\PublishArticle;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublishArticlesRequest;
use FriendsOfCat\BulkActions\Dispatcher;

class PublishArticlesController extends Controller
{
    protected $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function __invoke(PublishArticlesRequest $request)
    {
        $this->dispatcher->dispatch(PublishArticle::class, $request->articles());
    }
}
```

Alternatively, you may use the `BulkAction` facade:

```php
use FriendsOfCat\BulkActions\Facades\BulkAction;

BulkAction::dispatch('PublishArticle', $articles);
```

This will create a `FriendsOfCat\BulkActions\Models\Batch` instance associated with the authenticated user, and a `FriendsOfCat\BulkActions\Models\Job` instance for each model. A queue job will also be dispatched for each model, that will execute the action on that model.

You can view the status of a single batch job at any time. The available statuses are:

* `queued` (the job is queued but has not been started yet)
* `running` (the job is currently running)
* `failed` (the job failed)
* `complete` (the job completed without throwing an exception)

If an error or exception is thrown whilst processing the queue job, then the `status` on the `Job` model will be updated to `failed`.

### Events
Events are dispatched whenever a batch job’s status is updated. The events you may listen on are:

* `FriendsOfCat\BulkActions\Events\JobStarted`
* `FriendsOfCat\BulkActions\Events\JobFailed`
* `FriendsOfCat\BulkActions\Events\JobCompleted`

All events have one public `$job` property which is the current `FriendsOfCat\BulkActions\Models\Job` instance.

#### Broadcasting
The events also support [broadcasting][3] in case you want to offer dynamic, realtime updates. Events are broadcast on a private channel named `bulk-actions.batch.{batchId}`. The `{batchId}` value will be the UUID of the batch that the job belongs to. You may define an [authorization callback][4] to determine if the user should be able to receive updates for a particular batch:

```php
use App\User;
use FriendsOfCat\BulkActions\Models\Batch;

Broadcast::channel('bulk-actions.batch.{batch}', function (User $user, Batch $batch) {
    return $batch->user->is($user);
});
```

## Issues
If you have an issue or discover a bug, please open an issue at http://github.com/friendsofcat/bulk-actions/issues

## License
Licensed under the [MIT License](LICENSE).

[1]: https://nova.laravel.com/docs/3.0/actions/defining-actions.html
[2]: https://laravel.com/docs/8.x/queues
[3]: https://laravel.com/docs/8.x/broadcasting
[4]: https://laravel.com/docs/8.x/broadcasting#authorizing-channels
