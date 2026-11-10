<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HouseType;

class AcnooHouseTypeController extends Controller
{
    public function index()
    {
        $data = HouseType::where('status', 1)->latest()->get();

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data
        ]);
    }
}
