<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class LandlordDashboardController extends Controller
{
    public function __construct(protected DashboardService $service) {}

    public function index(Request $request)
    {
        $data = $this->service->summary($request->user(), $request->input('form_date'), $request->input('to_date'));

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data,
        ]);
    }

    public function property_chart(Request $request)
    {
        $data = $this->service->propertyChart($request->user(), $request->input('form_date'), $request->input('to_date'));

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data,
        ]);
    }

    public function account_chart(Request $request)
    {
        try {
            $data = $this->service->accountChart($request->user(), $request->input('duration'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => 'Invalid duration'], 400);
        }

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data,
        ]);
    }
}
