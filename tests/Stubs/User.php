<?php

namespace FriendsOfCat\BulkActions\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
