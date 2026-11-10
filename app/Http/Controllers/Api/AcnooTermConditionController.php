<?php

namespace App\Http\Controllers\Api;

use App\Models\Option;
use App\Http\Controllers\Controller;

class AcnooTermConditionController extends Controller
{
    public function index()
    {
        $term_condition = Option::where('key', 'term-condition')->first();
        return response()->json([
            'message' => 'Term Condition fetched successfully',
            'data' => $term_condition
        ]);
    }
}
