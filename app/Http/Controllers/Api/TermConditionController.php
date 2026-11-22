<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;

class TermConditionController extends Controller
{
    public function index()
    {
        $term_condition = Option::where('key', 'term-condition')->first();

        return response()->json([
            'message' => 'Term Condition fetched successfully',
            'data' => $term_condition,
        ]);
    }
}
