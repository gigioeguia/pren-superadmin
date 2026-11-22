<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Exceptions\PlanLimitExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Models\Property;
use App\Models\Rent;
use App\Models\Review;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(protected PropertyService $service) {}

    public function index(Request $request)
    {
        $actor = $request->user();
        $properties = $this->service->listForChannel(Channel::Api, $actor, $request->all());

        if ($request->has('no_paginate') && $request->no_paginate) {
            $responseData = [
                'data' => array_values($properties->items()),
            ];
        } else {
            $responseData = $properties;
        }

        return response()->json([
            'message' => 'Property data fetched successfully',
            'data' => $responseData,
        ]);
    }

    public function store(StorePropertyRequest $request)
    {
        $actor = $request->user();
        $data = $request->validated();
        $data['unit_files'] = $request->file('units');

        try {
            $property = $this->service->store($data, Channel::Api, $actor);
        } catch (PlanLimitExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 406);
        }

        return response()->json([
            'message' => 'Property saved successfully',
            'data' => $property->load('propertyDetail:property_id,property_info', 'landlord:id,name', 'category:id,name'),
        ]);
    }

    public function show(string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('view', $property);

        $property = $this->service->loadForShow($property, Channel::Api);

        $reviews = Review::with('tenant:id,name,image')->where('property_id', $property->id)->latest()->get();

        $canReview = Rent::where('tenant_id', auth()->user()->id)
            ->where('property_id', $property->id)
            ->exists();

        $total = $property->reviews()->count();
        $average = $property->reviews()->avg('review');

        return response()->json([
            'message' => 'Property data fetched successfully',
            'data' => [
                'property' => $property,
                'reviews' => $reviews,
                'can_review' => $canReview,
                'ratings' => [
                    'total' => $total,
                    'average' => $average,
                ],
            ],
        ]);
    }

    public function update(UpdatePropertyRequest $request, string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('update', $property);

        $actor = $request->user();
        $data = $request->validated();
        $data['unit_files'] = $request->file('units');

        try {
            $property = $this->service->update($property, $data, Channel::Api, $actor);
        } catch (PlanLimitExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 406);
        }

        return response()->json([
            'message' => 'Property updated successfully',
            'data' => $property->load('propertyDetail:property_id,property_info', 'landlord:id,name', 'category:id,name'),
        ]);
    }

    public function update_status(Request $request)
    {
        $property = Property::findOrFail($request->property_id);
        $this->authorize('update', $property);

        $property = $this->service->updateStatus($property, (bool) $request->is_published);

        return response()->json([
            'message' => 'Property status updated successfully',
            'data' => $property,
        ]);
    }

    public function destroy(string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('delete', $property);

        $this->service->delete($property, Channel::Api);

        return response()->json([
            'message' => __('Property deleted successfully'),
        ]);
    }
}
