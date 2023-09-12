<?php

namespace App\Traits;

use App\Http\Filters\AbstractFilter;
use Illuminate\Contracts\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Apply all filters.
     *
     * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
     * @param \App\Http\Filters\Filter              $filter
     *
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function scopeFilter(Builder $query, AbstractFilter $filter): Builder
    {
        return $filter->apply($query);
    }
}
