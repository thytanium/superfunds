<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = Company::paginate();

        return JsonResource::collection($records);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => ['required', 'max:255']]);

        $model = Company::create($request->all());

        return new JsonResource($model);
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return new JsonResource($company);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $request->validate(['name' => ['max:255']]);

        $company->update($request->all());

        return new JsonResource($company->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return new JsonResource($company);
    }
}
