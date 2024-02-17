<?php
declare(strict_types=1);

/*
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2021 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */
namespace Mavik\Image\ThumbnailsMaker\ResizeStrategy;

use Mavik\Image\ImageSize;
use Mavik\Image\ThumbnailsMaker\ImageArea;
use Mavik\Image\ThumbnailsMaker\ResizeStrategyInterface;
use Mavik\Image\Exception;

class Area implements ResizeStrategyInterface
{
    public function originalImageArea(ImageSize $originalSize, ImageSize $thumbnailSize): ImageArea
    {
        return new ImageArea(0, 0, $originalSize->width, $originalSize->height);
    }
    
    public function realThumbnailSize(ImageSize $originalSize, ImageSize $thumbnailSize): ImageSize
    {   
        if ($thumbnailSize->width && $thumbnailSize->height) {
            $thumbArea = $thumbnailSize->width * $thumbnailSize->height;
            $originArea = $originalSize->width * $originalSize->height;
            $ratio = sqrt($originArea/$thumbArea);
        } elseif ($thumbnailSize->width) {
            $ratio = $originalSize->width / $thumbnailSize->width;
        } elseif ($thumbnailSize->height) {
            $ratio = $originalSize->height / $thumbnailSize->height;
        } else {
            throw new Exception('Cannot calculate thumbnail size in ResizeStrategy\Area::thumbnailSize');
        }
      
        return new ImageSize(
            round($originalSize->width/$ratio),
            round($originalSize->height/$ratio)
        );
    }
}
