<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToUser
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToUser()
    {
        // Automatically set user_id on create
        static::creating(function (Model $model) {
            if (auth()->check() && !$model->user_id) {
                $model->user_id = auth()->id();
            }
        });

        // Global scope to filter by user (except for superadmins)
        static::addGlobalScope('user_scope', function (Builder $builder) {
            $user = auth()->user();
            
            // If no user is authenticated, don't apply scope
            if (!$user) {
                return;
            }

            // Superadmin sees everything
            if ($user->hasRole('superadmin')) {
                return;
            }

            // Regular users only see their own data
            $builder->where(function ($query) use ($user) {
                $query->where($query->getModel()->getTable() . '.user_id', $user->id);
            });
        });
    }

    /**
     * Get the user that owns the model
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Scope a query to include all users' data (bypass user scope)
     * Useful for superadmin operations
     */
    public function scopeWithAllUsers(Builder $query)
    {
        return $query->withoutGlobalScope('user_scope');
    }

    /**
     * Scope a query to only show data from a specific user
     */
    public function scopeForUser(Builder $query, $userId)
    {
        return $query->withoutGlobalScope('user_scope')->where('user_id', $userId);
    }
}
