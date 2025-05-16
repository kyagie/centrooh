<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Illuminate\Support\Str;

class BillboardImageService
{
    /**
     * Adds billboard metadata text to the image.
     *
     * @param  \Illuminate\Http\UploadedFile  $image
     * @param  array  $billboardInfo
     * @param  string  $imageName
     * @return string
     */
    public function addTextToImage($image, array $billboardInfo, string $imageName)
    {
        $img = Image::read($image);
        $imgWidth = $img->width();
        $imgHeight = $img->height();

        $x = 0;
        $y = $imgHeight - 160;
        $currentDate = date('Y-m-d(D) H:i');

        $text = <<<EOT
Latitude: {$billboardInfo['latitude']}
Longitude: {$billboardInfo['longitude']}
{$currentDate}
EOT;

        $img->drawRectangle(
            $x,
            $y,
            fn(RectangleFactory $rectangle) =>
            $rectangle->size($imgWidth, 160)->background('#0000')
        );

        $img->text($text, $imgWidth / 2, $y + 60, function (FontFactory $font) {
            $font->filename(public_path('assets/fonts/Exo2-Bold.otf'));
            $font->size(38);
            $font->color('#FFDB58');
            $font->align('center');
            $font->valign('center');
        });

        $img->save(public_path("images/{$imageName}"));

        return $imageName;
    }

    /**
     * Generate image file name with timestamp.
     *
     * @param  string  $name
     * @param  string  $extension
     * @return string
     */
    public function generateImageName(string $name, string $extension): string
    {
        $timestamp = now()->format('Ymd_His');
        return Str::snake($name) . "_{$timestamp}.{$extension}";
    }
}
