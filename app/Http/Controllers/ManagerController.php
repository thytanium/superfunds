<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = Manager::paginate();

        return JsonResource::collection($records);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => ['required', 'max:255']]);

        $model = Manager::create($request->all());

        return new JsonResource($model);
    }

    /**
     * Display the specified resource.
     */
    public function show(Manager $manager)
    {
        return new JsonResource($manager);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Manager $manager)
    {
        $request->validate(['name' => ['max:255']]);

        $manager->update($request->all());

        return new JsonResource($manager->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manager $manager)
    {
        $manager->delete();

        return new JsonResource($manager);
    }
}
