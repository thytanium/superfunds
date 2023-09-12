<?php

namespace App\Listeners;

use App\Events\DuplicateFundWarning;
use App\Models\PotentialDuplicateFund;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreDuplicateFundWarning
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DuplicateFundWarning $event): void
    {
        PotentialDuplicateFund::create([
            'related_fund_id' => $event->related->id,
            'offending_fund_id' => $event->offending->id,
            'offending_fund_name' => $event->offending->name,
            'offending_manager_name' => $event->offending->manager->name,
        ]);
    }
}
