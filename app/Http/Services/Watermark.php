<?php
/**
 * Created by PhpStorm.
 * User: artem
 * Date: 24.01.2021
 * Time: 14:00
 */

namespace App\Http\Services;

use Illuminate\Support\Facades\Storage;

class Watermark
{
    public function addWatermark($main_color, $mime_type, $photo_path, $file_name)
    {
        switch ($main_color) {
            case 'red':
                $storage_path = Storage::disk('watermarks')->getAdapter()->getPathPrefix();
                $watermark = $storage_path . 'black.jpg';
                break;
            case 'green':
                $storage_path = Storage::disk('watermarks')->getAdapter()->getPathPrefix();
                $watermark = $storage_path . 'red.jpg';
                break;
            case 'blue':
                $storage_path = Storage::disk('watermarks')->getAdapter()->getPathPrefix();
                $watermark = $storage_path . 'yellow.jpg';
                break;
            default:
                $storage_path = Storage::disk('watermarks')->getAdapter()->getPathPrefix();
                $watermark = $storage_path . 'yellow.jpg';
        }

        if ($mime_type == 'image/jpeg') {
            $img = imagecreatefromjpeg($photo_path);
        } else {
            $img = imagecreatefrompng($photo_path);
        }

        $water = imagecreatefromjpeg($watermark);
        $im = $this->create_watermark($img, $water, 50);

        imagejpeg($im, '/result.jpg');

        $file = sys_get_temp_dir() . '/result.jpg';

        if(!file_exists($file)){ // file does not exist
            die('file not found');
        } else {
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$file_name");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");

            readfile($file);
        }
    }

    public function create_watermark($main_img_obj, $watermark_img_obj, $alpha_level = 100)
    {
        $watermark_width = imagesx($watermark_img_obj);
        $watermark_height = imagesy($watermark_img_obj);

        $dest_x = imagesx($main_img_obj) - $watermark_width;
        $dest_y = imagesy($main_img_obj) - $watermark_height;
        imagecopymerge($main_img_obj, $watermark_img_obj, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $alpha_level);

        return $main_img_obj;
    }


    public function getMainColor($list_main_rgb, $test)
    {
        $distances = [];
        foreach ($list_main_rgb as $key => $value) {
            $distances[$key] = intval($this->getDistanceFromColor($value, $test));
        }
        $distances = array_flip($distances);
        ksort($distances);
        return array_shift($distances);
    }

    public function getDistanceFromColor($a, $b)
    {
        list($r1, $g1, $b1) = $a;
        list($r2, $g2, $b2) = $b;

        return sqrt(pow($r2 - $r1, 2) + pow($g2 - $g1, 2) + pow($b2 - $b1, 2));
    }

    public function getFavoriteColorRGB($rgb)
    {
        $rgbarr = explode(",", $rgb, 3);

        $favorite_color[] = $rgbarr[0];
        $favorite_color[] = $rgbarr[1];
        $favorite_color[] = $rgbarr[2];
        return $favorite_color;
    }

    public function getMainColorsRGB()
    {
        $list_main_rgb['red'] = [255, 0, 0];
        $list_main_rgb['blue'] = [0, 0, 255];
        $list_main_rgb['green'] = [0, 255, 0];
        return $list_main_rgb;
    }

    public function beliefmedia_dominant_color($image, $mime_type, $array = false, $format = '%d, %d, %d')
    {
        if ($mime_type == 'image/jpeg') {
            $i = imagecreatefromjpeg($image);
        } else {
            $i = imagecreatefrompng($image);
        }
        $r_total = 0;
        $g_total = 0;
        $b_total = 0;
        $total = 0;
        for ($x = 0; $x < imagesx($i); $x++) {
            for ($y = 0; $y < imagesy($i); $y++) {
                $rgb = imagecolorat($i, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $r_total += $r;
                $g_total += $g;
                $b_total += $b;
                $total++;
            }
        }
        $r = round($r_total / $total);
        $g = round($g_total / $total);
        $b = round($b_total / $total);
        $rgb = ($array) ? array('r' => $r, 'g' => $g, 'b' => $b) : sprintf($format, $r, $g, $b);
        return $rgb;
    }
}
