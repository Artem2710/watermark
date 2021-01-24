<?php

namespace App\Http\Controllers;

use App\Http\Services\Watermark;
use Illuminate\Http\Request;

class WatermarksController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('watermarks');
    }

    public function create(Request $request)
    {
        $watermark = new Watermark();
        $files = $request->files;
        foreach ($files as $file) {
            $path = $file->getPathname();
            $rgb = $watermark->beliefmedia_dominant_color($path, $file->getMimeType());
            $favorite_color_rgb = $watermark->getFavoriteColorRGB($rgb);
            $list_main_rgb = $watermark->getMainColorsRGB();
            $main_color = $watermark->getMainColor($list_main_rgb, $favorite_color_rgb);

            $watermark->addWatermark($main_color, $file->getMimeType(), $path, $file->getClientOriginalName());

        }
        return view('watermarks');
    }

}
