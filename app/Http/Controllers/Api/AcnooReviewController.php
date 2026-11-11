<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rent;
use App\Models\Review;
use Illuminate\Http\Request;

class AcnooReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|integer|exists:properties,id',
            'review' => 'required|numeric|min:0|max:5',
            'comment' => 'nullable|string',
        ]);
        $rent = Rent::where('property_id', $request->property_id)
            ->where('tenant_id', auth()->id())
            ->first();
        if (! $rent) {
            return response()->json([
                'message' => 'You are not allowed to review this property because you have no rent record.',
            ], 403);
        }

        $data = Review::create($request->except('tenant_id') + [
            'rent_id' => $rent->id,
            'tenant_id' => auth()->user()->id,
        ]);

        return response()->json([
            'message' => 'Review store successfully',
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'property_id' => 'required|integer|exists:properties,id',
            'review' => 'required|numeric|min:0|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = Review::findOrFail($id);

        $review->update($request->except('tenant_id') + [
            'tenant_id' => auth()->user()->id,
        ]);

        return response()->json([
            'message' => 'Review updated successfully',
            'data' => $review,
        ]);
    }
}
