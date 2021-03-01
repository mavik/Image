<?php
/* 
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2021 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */

namespace Mavik\Image\Tests;

class CompareImages
{
    public static function distance(string $image1, string $image2): int
    {
        exec("gm compare -metric MSE {$image1} {$image2}", $output);
        foreach ($output as $line) {
            preg_match('/Total:\s+(0\.\d+)\s+\d+\.\d+/', $line, $matches);
            if (!empty($matches)) {
                return round($matches[1] * 100);
            }
        }
        throw new \Exception("Images '{$image1}' and '{$image2}' cannot be compared.");
    }
}