<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreServiceRequest;
use App\Http\Requests\V1\UpdateServiceRequest;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ServiceResource::collection(Service::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request)
    {
        $service = Service::create($request->validated());
        return new ServiceResource($service);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return new ServiceResource($service);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update($request->validated());
        return new ServiceResource($service);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return response()->noContent();
    }
}
