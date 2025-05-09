<?php

namespace App\Helpers;

use Intervention\Image\Facades\Image;


class ImageHelper
{
    public static function resizeAndSave($file, $path, $filename, $width = 1920, $height = 600)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        Image::make($file)
            ->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->save($path . $filename);

        return $filename;
    }
}
