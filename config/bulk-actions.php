<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bulk Actions User Model
    |--------------------------------------------------------------------------
    |
    | The fully-qualified namespace of the user model in your application. Bulk
    | actions are associated with a user so you can see who initiated them.
    |
    */

    'user_model' => env('BULK_ACTIONS_USER_MODEL', class_exists('App\Models\User') ? 'App\Models\User' : 'App\User'),

];
