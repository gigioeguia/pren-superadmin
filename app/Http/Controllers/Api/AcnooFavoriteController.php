<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooFavoriteController extends Controller
{
    public function index()
    {
        $wishlists = Favorite::with([
            'property:id,category_id,landlord_id,status,room_info,rent_info,address_info,cover_image',
            'property.propertyDetail:property_id,property_info',
            'property.landlord:id,name',
            'property.category:id,name',
        ])
            ->where('tenant_id', auth()->id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $wishlists
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|integer|exists:properties,id',
        ]);

        $exists = Favorite::where('tenant_id', auth()->id())
            ->where('property_id', $request->property_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This property is already in your wishlist',
            ], 422);
        } else {

            $favorite = Favorite::create($request->except('tenant_id') + [
                'tenant_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Wishlist added successfully',
                'data' => $favorite
            ]);
        }
    }

    public function destroy(string $id)
    {
        $wishlist = Favorite::findOrFail($id);

        $wishlist->delete();

        return response()->json([
            'message' => 'Wishlist deleted successfully',
        ]);
    }
}
