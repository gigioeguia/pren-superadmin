<?php

namespace App\Http\Controllers;

use App\Models\Highlight;

class HighlightController extends Controller
{
    public function show(string $slug)
    {
        $page_data = get_option('manage-pages');
        $highlight = Highlight::where('slug', $slug)->firstOrFail();

        return view('web.components.show-highlight', compact('page_data', 'highlight'));
    }
}
