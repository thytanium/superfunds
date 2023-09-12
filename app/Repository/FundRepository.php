<?php

namespace App\Repository;

use App\Events\DuplicateFundWarning;
use App\Http\Filters\FundFilter;
use App\Models\Fund;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class FundRepository
{
    /**
     * Create a FundRepository instance.
     *
     * @param \App\Http\Filters\FundFilter $filter
     */
    public function __construct(protected FundFilter $filter)
    {
    }

    /**
     * Fetch all fund records.
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function all(): Paginator
    {
        return Fund::filter($this->filter)
            ->with('company', 'manager')
            ->paginate();
    }

    /**
     * Look for funds that have the same name, or alias, for the same manager.
     *
     * @param \App\Models\Fund $fund
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findSimilar(Fund $fund): Collection
    {
        return Fund::where('id', '!=', $fund->id)
            ->where(
                fn($query) => $query
                    ->whereName($fund->name)
                    ->orWhereJsonContains('aliases', $fund->name),
            )
            ->whereManagerId($fund->manager_id)
            ->get();
    }

    /**
     * Create new fund.
     *
     * @param array $data
     * @return \App\Models\Fund
     */
    public function create(array $data): Fund
    {
        $created = Fund::create($data);

        // look for similar funds and fire an event if any found
        $similar = $this->findSimilar($created)->each(
            fn($fund) => event(
                new DuplicateFundWarning(offending: $created, related: $fund),
            ),
        );

        return $created;
    }

    /**
     * Updates a fund.
     *
     * @param \App\Models\Fund  $fund The fund in question
     * @param array             $data The modified data
     * @return \App\Models\Fund
     */
    public function update(Fund $fund, array $data): Fund
    {
        $fund->update($data);

        return $fund->refresh();
    }

    /**
     * Delete a fund.
     *
     * @param \App\Models\Fund $fund The fund in question
     * @return \App\Models\Fund
     */
    public function destroy(Fund $fund): Fund
    {
        $fund->delete();

        return $fund;
    }
}
