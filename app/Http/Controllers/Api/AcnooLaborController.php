<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Labor;
use Illuminate\Http\Request;

class AcnooLaborController extends Controller
{
    public function index(Request $request)
    {
        $query = Labor::select('id', 'name', 'phone')
            ->where('landlord_id', auth()->id())
            ->latest();
        if ($request->has('no_paginate') && $request->no_paginate == true) {
            $labors = $query->select('id', 'name')->get();
            $responseData = [
                'data' => $labors,
            ];
        } else {
            $labors = $query->paginate($request->per_page ?? 10);
            $responseData = $labors;
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $responseData
        ]);
    }

    public function store(Request $request)
    {
        $plan = plan_data()->plan;
        $labor_count = Labor::where('landlord_id', auth()->id())->count();

        if ($plan->number_of_labor !== -1 && $labor_count >= $plan->number_of_labor) {
            return response()->json(['message' => __('Your Labor addition limit has been reached. Your current limit is ' . $plan->number_of_labor)], 406);
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:labors,email',
            'phone' => 'nullable|array',
            'mobile_code' => 'nullable|string',
            'mobile_no' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'gender' => 'nullable|string',
        ]);

        $labor = Labor::create($request->except('landlord_id', 'phone') + [
            'landlord_id' => auth()->id(),
            'phone' => $request->phone
        ]);

        sendNotification($labor->landlord->id, notify_users([$labor->landlord->id]), landlord_url: route('landlord.labors.index', ['id' => $labor->id]),  landlord_msg: __('You have created a new labor.'));

        return response()->json([
            'message' => 'Labor created successfully',
            'data' => $labor
        ]);
    }

    public function show(string $id)
    {
        $labor = Labor::findOrFail($id);

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $labor
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:labors,email,' . $id,
            'phone' => 'nullable|array',
            'mobile_code' => 'nullable|string',
            'mobile_no' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'gender' => 'nullable|string',
        ]);

        $labor = Labor::findOrFail($id);

        $labor->update($request->except('landlord_id', 'phone') + [
            'landlord_id' => auth()->id(),
            'phone' => $request->phone
        ]);

        return response()->json([
            'message' => 'Labor updated successfully',
            'data' => $labor
        ]);
    }

    public function destroy(string $id)
    {
        $labor = Labor::findOrFail($id);
        $labor->delete();

        return response()->json([
            'message' => 'Labor deleted successfully',
        ]);
    }
}
