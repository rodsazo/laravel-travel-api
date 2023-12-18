<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TourListRequest;
use App\Http\Resources\TourResource;
use App\Models\Travel;

/**
 * @group Public endpoints
 */
class TourController extends Controller
{

    /**
     * GET Travel Tours
     *
     * Returns the paginated list of tours by travel slug.
     *
     * @urlParam travel_slug string Travel slug. Example: first-travel
     *
     * @bodyParam priceFrom number. Example: "144.99"
     * @bodyParam priceTo number. Example: "200.30"
     * @bodyParam dateFrom date. Example: "2023-12-15"
     * @bodyParam dateTo date. Example: "2023-12-31"
     * @bodyParam sortBy string. Example: "price"
     * @bodyParam sortOrder string. Example: "asc" or "desc"
     *
     * @response {"data":[{"id":"9958e389-5edf-48eb-8ecd-e058985cf3ce","name":"Tour on Sunday","starting_date":"2023-06-11","ending_date":"2023-06-16", ...}
     *
     * @param Travel $travel
     * @param TourListRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Travel $travel, TourListRequest $request)
    {

        $tours = $travel->tours()

            ->when($request->priceFrom, function ($query) use ($request) {
                $query->where('price', '>=', $request->priceFrom * 100);
            })

            ->when($request->priceTo, function ($query) use ($request) {
                $query->where('price', '<=', $request->priceTo * 100);
            })

            ->when($request->dateFrom, function ($query) use ($request) {
                $query->where('starting_date', '>=', $request->dateFrom);
            })

            ->when($request->dateTo, function ($query) use ($request) {
                $query->where('starting_date', '<=', $request->dateTo);
            })

            ->when($request->sortBy, function ($query) use ($request) {
                $query->orderBy($request->sortBy, $request->sortOrder);
            })

            ->orderBy('starting_date')
            ->paginate(10);

        return TourResource::collection($tours);
    }
}
