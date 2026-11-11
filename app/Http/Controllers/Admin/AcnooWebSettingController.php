<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AcnooWebSettingController extends Controller
{
    use HasUploader;

    public function index()
    {
        $page_data = get_option('manage-pages');

        return view('admin.website-setting.manage-pages', compact('page_data'));
    }

    public function update(Request $request, $key)
    {
        $option = Option::where('key', 'manage-pages')->first();
        Option::updateOrCreate(
            ['key' => 'manage-pages'],
            ['value' => [
                'headings' => $request->except('_token', '_method', 'slider_image', 'contact_us_img', 'footer_socials_icons', 'about_image', 'get_app_apple_store_img', 'get_app_play_store_img', 'web_logo_one', 'web_logo_two', 'soft_mng_subtitle_one_icon', 'soft_mng_subtitle_two_icon', 'soft_mng_subtitle_three_icon', 'soft_mng_subtitle_one_img', 'soft_mng_subtitle_two_img', 'soft_mng_subtitle_three_img'),

                'slider_image' => $request->slider_image ? $this->upload($request, 'slider_image', $option->value['slider_image']) : $option->value['slider_image'] ?? null,
                'contact_us_img' => $request->contact_us_img ? $this->upload($request, 'contact_us_img', $option->value['contact_us_img']) : $option->value['contact_us_img'] ?? null,
                'about_image' => $request->about_image ? $this->upload($request, 'about_image', $option->value['about_image']) : $option->value['about_image'] ?? null,
                'get_app_apple_store_img' => $request->get_app_apple_store_img ? $this->upload($request, 'get_app_apple_store_img', $option->value['get_app_apple_store_img']) : $option->value['get_app_apple_store_img'] ?? null,
                'get_app_play_store_img' => $request->get_app_play_store_img ? $this->upload($request, 'get_app_play_store_img', $option->value['get_app_play_store_img']) : $option->value['get_app_play_store_img'] ?? null,
                'web_logo_one' => $request->web_logo_one ? $this->upload($request, 'web_logo_one', $option->value['web_logo_one']) : $option->value['web_logo_one'] ?? null,
                'web_logo_two' => $request->web_logo_two ? $this->upload($request, 'web_logo_two', $option->value['web_logo_two']) : $option->value['web_logo_two'] ?? null,
                'soft_mng_subtitle_one_icon' => $request->soft_mng_subtitle_one_icon ? $this->upload($request, 'soft_mng_subtitle_one_icon', $option->value['soft_mng_subtitle_one_icon']) : $option->value['soft_mng_subtitle_one_icon'] ?? null,
                'soft_mng_subtitle_two_icon' => $request->soft_mng_subtitle_two_icon ? $this->upload($request, 'soft_mng_subtitle_two_icon', $option->value['soft_mng_subtitle_two_icon']) : $option->value['soft_mng_subtitle_two_icon'] ?? null,
                'soft_mng_subtitle_three_icon' => $request->soft_mng_subtitle_three_icon ? $this->upload($request, 'soft_mng_subtitle_three_icon', $option->value['soft_mng_subtitle_three_icon']) : $option->value['soft_mng_subtitle_three_icon'] ?? null,
                'soft_mng_subtitle_one_img' => $request->soft_mng_subtitle_one_img ? $this->upload($request, 'soft_mng_subtitle_one_img', $option->value['soft_mng_subtitle_one_img']) : $option->value['soft_mng_subtitle_one_img'] ?? null,
                'soft_mng_subtitle_two_img' => $request->soft_mng_subtitle_two_img ? $this->upload($request, 'soft_mng_subtitle_two_img', $option->value['soft_mng_subtitle_two_img']) : $option->value['soft_mng_subtitle_two_img'] ?? null,
                'soft_mng_subtitle_three_img' => $request->soft_mng_subtitle_three_img ? $this->upload($request, 'soft_mng_subtitle_three_img', $option->value['soft_mng_subtitle_three_img']) : $option->value['soft_mng_subtitle_three_img'] ?? null,

                'footer_socials_icons' => $request->footer_socials_icons ? $this->multipleUpload($request, 'footer_socials_icons') : $option->value['footer_socials_icons'] ?? null,

            ],
            ]);

        Cache::forget('manage-pages');

        return response()->json(__('Pages updated successfully.'));
    }
}
