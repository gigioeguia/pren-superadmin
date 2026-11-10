<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlanController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:plans-read')->only('index');
        $this->middleware('permission:plans-create')->only('create', 'store');
        $this->middleware('permission:plans-update')->only('edit', 'update');
        $this->middleware('permission:plans-delete')->only('destroy');
    }

    public function index()
    {
        $plans = Plan::latest()->paginate(20);
        return view('admin.plans.index',  compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subscriptionName' => 'required|string|max:255',
            'duration' => 'required|string',
            'duration_type' => 'required|string',
            'offerPrice' => 'required|integer|min:0',
            'number_of_property' => 'nullable|numeric',
            'number_of_labor' => 'nullable|numeric',
            'number_of_tenant' => 'nullable|numeric',
            'features' => 'nullable|array',
            'features.*' => 'array',
            'features.*.*' => 'string',
            'status' => 'nullable|in:on',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg',
        ]);

        if (empty($request->property_unlimited_status)) {
            $request->validate([
                'number_of_property' => 'required|numeric',
            ]);
        }
        if (empty($request->labor_unlimited_status)) {
            $request->validate([
                'number_of_labor' => 'required|numeric',
            ]);
        }
        if (empty($request->tenant_unlimited_status)) {
            $request->validate([
                'number_of_tenant' => 'required|numeric',
            ]);
        }

        $features = [];
        if ($request->has('features')) {
            foreach ($request->features as $feature) {
                $features[] = [
                    'name' => $feature['name'] ?? '',
                    'status' => isset($feature['status']) ? (int) $feature['status'] : 0,
                ];
            }
        }

        Plan::create($request->except('status', 'image', 'number_of_property', 'number_of_tenant', 'number_of_labor', 'features') + [
            'status' => $request->status ? 1 : 0,
            'image' => $request->image ? $this->upload($request, 'image') : null,
            'number_of_property' => $request->property_unlimited_status ? -1 : $request->number_of_property,
            'number_of_tenant' => $request->tenant_unlimited_status ? -1 : $request->number_of_tenant,
            'number_of_labor' => $request->labor_unlimited_status ? -1 : $request->number_of_labor,
            'features' => $features,
        ]);

        return response()->json([
            'message' => 'Plan Created Successfully',
            'redirect' => route('admin.plans.index')
        ]);
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'subscriptionName' => 'required|string|max:255',
            'duration' => 'required|string',
            'duration_type' => 'required|string',
            'offerPrice' => 'required|integer|min:0',
            'number_of_property' => 'nullable|numeric',
            'number_of_labor' => 'nullable|numeric',
            'number_of_tenant' => 'nullable|numeric',
            'number_of_rent' => 'nullable|numeric',
            'number_of_maintenance' => 'nullable|numeric',
            'features' => 'nullable|array',
            'features.*' => 'array',
            'features.*.*' => 'string',
            'status' => 'nullable|in:on',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg',
        ]);

        if (empty($request->property_unlimited_status)) {
            $request->validate([
                'number_of_property' => 'required|numeric',
            ]);
        }
        if (empty($request->labor_unlimited_status)) {
            $request->validate([
                'number_of_labor' => 'required|numeric',
            ]);
        }
        if (empty($request->tenant_unlimited_status)) {
            $request->validate([
                'number_of_tenant' => 'required|numeric',
            ]);
        }

        $features = [];
        if ($request->has('features')) {
            foreach ($request->features as $feature) {
                $features[] = [
                    'name' => $feature['name'] ?? '',
                    'status' => isset($feature['status']) ? (int) $feature['status'] : 0,
                ];
            }
        }

        $plan->update($request->except('status', 'image', 'number_of_property', 'number_of_labor', 'number_of_tenant', 'features') + [
            'status' => $request->status ? 1 : 0,
            'image' => $request->image ? $this->upload($request, 'image') : $plan->image,
            'number_of_property' => $request->property_unlimited_status ? -1 : $request->number_of_property,
            'number_of_tenant' => $request->tenant_unlimited_status ? -1 : $request->number_of_tenant,
            'number_of_labor' => $request->labor_unlimited_status ? -1 : $request->number_of_labor,
            'features' => $features,
        ]);

        return response()->json([
            'message' => 'Plan Updated Successfully',
            'redirect' => route('admin.plans.index')
        ]);
    }

    public function destroy(Plan $plan)
    {
        if (file_exists($plan->image)) {
            Storage::delete($plan->image);
        }

        $plan->delete();

        return response()->json([
            'message'   => __('Plan deleted successfully'),
            'redirect'  => route('admin.plans.index')
        ]);
    }
}
