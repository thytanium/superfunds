<?php

namespace App\Http\Filters;

use Illuminate\Contracts\Database\Query\Builder;

class FundFilter extends AbstractFilter
{
    /**
     * Filter funds by name.
     *
     * @param string $value
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function name(string $value): Builder
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Filter funds by start_year.
     *
     * @param string $value
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function startYear(string $value): Builder
    {
        return $this->builder->whereStartYear($value);
    }

    /**
     * Filter funds by fund manager name.
     *
     * @param string $value
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function managerName(string $value): Builder
    {
        return $this->builder->whereHas(
            'manager',
            fn(Builder $query) => $query->where(
                'name',
                'like',
                '%' . $value . '%',
            ),
        );
    }

    /**
     * Filter funds by fund manager ID.
     *
     * @param string $value
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function managerId(string $value): Builder
    {
        return $this->builder->whereManagerId($value);
    }
}
