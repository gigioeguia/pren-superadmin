<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class AcnooAboutUsController extends Controller
{
    public function index()
    {
        $data = get_option('manage-pages');

        $headings = $data['headings'] ?? [];

        $filtered = [
            'about_us_title' => $headings['about_us_title'] ?? null,
            'about_desc' => $headings['about_desc'] ?? null,
            'about_image' => $data['about_image'] ?? null,
        ];

        return response()->json([
            'message' => 'About Us fetched successfully',
            'data' => $filtered,
        ]);
    }
}
