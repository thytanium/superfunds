<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Repository\FundRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FundRepository $repository)
    {
        $records = $repository->all();
        return JsonResource::collection($records);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, FundRepository $repository)
    {
        $request->validate([
            'name' => ['required', 'max:255'],
            'start_year' => ['required', 'integer'],
            'aliases' => ['array'],
            'manager_id' => ['required', 'exists:managers,id'],
            'company_id' => ['required', 'exists:companies,id'],
        ]);

        $fund = $repository->create($request->all());

        return new JsonResource($fund);
    }

    /**
     * Display the specified resource.
     */
    public function show(Fund $fund)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        Fund $fund,
        FundRepository $repository,
    ) {
        $request->validate([
            'name' => ['max:255'],
            'start_year' => ['integer'],
            'aliases' => ['array'],
            'manager_id' => ['exists:managers,id'],
            'company_id' => ['exists:companies,id'],
        ]);

        $updatedFund = $repository->update($fund);

        return new JsonResource($updatedFund);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fund $fund, FundRepository $repository)
    {
        $deletedFund = $repository->destroy($fund);

        return new JsonResource($deletedFund);
    }
}
