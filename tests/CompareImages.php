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
        $ch = curl_init('https://api.deepai.org/api/image-similarity');
        $file1 = file_exists($image1) ? new \CURLFile($image1) : $image1;
        $file2 = file_exists($image2) ? new \CURLFile($image2) : $image2;
        $data = [
            'image1' => $file1,
            'image2' => $file2,
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key:8c6c6720-752f-4233-98ef-930769dcc61f']); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        $result = json_decode($response);
        return $result->output->distance;        
    }
}