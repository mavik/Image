<?php
/*
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2021 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */
namespace Mavik\Image\ThumbnailsMaker\ResizeStrategy;

use Mavik\Image\ThumbnailsMaker\ResizeStrategyInterface;
use Mavik\Image\ImageSize;
use Mavik\Image\ThumbnailsMaker\ImageArea;

class Stretch implements ResizeStrategyInterface
{
    public function originalImageArea(ImageSize $originalSize, ImageSize $thumbnailSize): ImageArea
    {
        return new ImageArea(0, 0, $originalSize->width, $originalSize->height);
    }
    
    public function thumbnailSize(ImageSize $originalSize, ImageSize $thumbnailSize): ImageSize
    {
        return $thumbnailSize;
    }
}
