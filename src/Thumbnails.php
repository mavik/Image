<?php
/*
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2021 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */
namespace Mavik\Image;

/**
 * Create thumbnails for image
 */
class Thumbnails
{
    public function make(string $src, int $width = 0, int $height = 0, array $ratios = [1])
    {
        $image = new Image($src);
        $thumbnails = [];
        foreach ($ratios as $ratio) {
            $imageWidth = $image->getWidth();
            $imageHeight = $image->getHeight();
            $realWidth = $width * $ratio;
            $realHeight = $height * $ratio;
            if ($realWidth >= $imageWidth && $realHeight >= $imageHeight) {
                continue;
            } 
            list() = $this->resizeStrategy->resizeParams(
                $imageWidth,
                $imageHeight,
                $realWidth,
                $realHeight
            );
            
        }
    }
}
