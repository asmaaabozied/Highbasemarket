<?php

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;

trait IncludedDataScopes
{
    public function scopeWithinSavedList(Builder $query, ?Branch $branch = null): void
    {
        if (! $branch instanceof \App\Models\Branch) {
            return;
        }

        $query->with('listItem', function ($query) use ($branch): void {
            $query->whereHas('savedList', function ($query) use ($branch): void {
                $query->where('saved_lists.branch_id', $branch->id);
            });
        });
    }

    public function scopeWithinCart(Builder $query, ?Branch $branch = null): void
    {
        if (! $branch instanceof \App\Models\Branch) {
            return;
        }

        $query->with('cart', function ($query) use ($branch): void {
            $query->where('carts.branch_id', $branch->id);
        });
    }

    public function scopeNotWithinCart(Builder $query, ?Branch $branch = null): void
    {
        if (! $branch instanceof \App\Models\Branch) {
            return;
        }

        $query->whereDoesntHave('cart', function ($query) use ($branch): void {
            $query->where('carts.branch_id', $branch->id);
        });
    }
}
