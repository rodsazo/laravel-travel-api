<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TourRequest;
use App\Http\Resources\TourResource;
use App\Models\Travel;

/**
 * @group Admin endpoints
 */
class TourController extends Controller
{
    /**
     * POST Tour
     *
     * Creates a new tour record.
     *
     * @authenticated
     *
     * @bodyParam name number. Example: "A night-tour of Cuzco"
     * @bodyParam starting_date date. Example: "2023-12-10"
     * @bodyParam ending_date date. Example: "2023-12-20"
     * @bodyParam price number. Example: "123.25"
     *
     * @response {"data":{"id":"996a36ca-2693-4901-9c55-7136e68d81d5","name":"My new tour","starting_date":"2023-12-15", ...}
     */
    public function store(Travel $travel, TourRequest $request)
    {
        $tour = $travel->tours()->create($request->validated());

        return new TourResource($tour);
    }
}
