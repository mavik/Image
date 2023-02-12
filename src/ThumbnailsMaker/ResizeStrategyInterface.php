<?php
/*
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2022 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */
namespace Mavik\Image\ThumbnailsMaker;

use Mavik\Image\ThumbnailsMaker\ImageArea;
use Mavik\Image\ImageSize;

interface ResizeStrategyInterface 
{
    public function originalImageArea(ImageSize $originalSize, ImageSize $thumbnailSize): ImageArea; 
    public function realThumbnailSize(ImageSize $originalSize, ImageSize $thumbnailSize): ImageSize;
}
