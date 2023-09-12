<?php

namespace App\Http\Controllers;

use App\Models\PotentialDuplicateFund;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PotentialDuplicateFundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = PotentialDuplicateFund::paginate();

        return JsonResource::collection($records);
    }
}
