<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\PotentialDuplicateFund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FundTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the application detects potential duplicate funds.
     */
    public function test_it_detects_potential_duplicate_funds(): void
    {
        $this->seed();

        // create initial fund
        $initial = Fund::create([
            'name' => 'Initial Fund',
            'start_year' => 1999,
            'aliases' => ['Fund Alias'],
            'manager_id' => 1,
            'company_id' => 1,
        ]);

        // create a new fund with existing name and manager
        $response = $this->post('/api/funds', [
            'name' => 'Initial Fund',
            'start_year' => 2023,
            'aliases' => ['Alias 1'],
            'manager_id' => 1,
            'company_id' => 1,
        ]);

        // assert fund is created successfully
        $response->assertStatus(201);

        $potentialDuplicate = PotentialDuplicateFund::first();

        $this->assertEquals($initial->id, $potentialDuplicate->related_fund_id);
        $this->assertEquals(
            $initial->id + 1,
            $potentialDuplicate->offending_fund_id,
        );
        $this->assertEquals(
            'Initial Fund',
            $potentialDuplicate->offending_fund_name,
        );
    }
}
