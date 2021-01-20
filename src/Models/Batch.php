<?php

namespace FriendsOfCat\BulkActions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Batch extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'bulk_action_batches';

    /**
     * {@inheritDoc}
     */
    protected $keyType = 'string';

    /**
     * {@inheritDoc}
     */
    public $incrementing = false;

    /**
     * Get the jobs for the batch.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Get the user that initiated the batch.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Config::get('bulk-actions.user_model'));
    }

    /**
     * {@inheritDoc}
     */
    protected static function booted(): void
    {
        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Str::orderedUuid());
        });
    }
}
