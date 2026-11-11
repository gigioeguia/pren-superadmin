<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AcnooTermConditionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:term-conditions-read')->only('index');
        $this->middleware('permission:term-conditions-update')->only('store');
    }

    public function index()
    {
        $term_condition = Option::where('key', 'term-condition')->first();

        return view('admin.term-condition.index', compact('term_condition'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'term_title' => 'required|string',
            'description' => 'required|string',
        ]);

        $term_condition = Option::updateOrCreate(
            ['key' => 'term-condition'],
            ['value' => [
                'term_title' => $request->term_title,
                'description' => $request->description,
            ]]
        );

        if ($term_condition) {
            Cache::forget($term_condition->key);
        }

        return response()->json([
            'message' => __('Term And Condition updated successfully'),
            'redirect' => route('admin.term-conditions.index'),
        ]);
    }
}
