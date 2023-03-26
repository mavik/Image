<?php
/*
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2022 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */
namespace Mavik\Image;

use Mavik\Image\ThumbnailsMaker\ResizeStrategyInterface;

/**
 * Create thumbnails for image
 */
class ThumbnailsMaker
{
    /** @var ResizeStrategyInterface */
    private $resizeStrategy;

    public function __construct(ResizeStrategyInterface $resizeStrategy)
    {
        $this->resizeStrategy = $resizeStrategy;
    }
    
    /**
     * Create thumbnails from $originalSrc
     * 
     * You can create a few thumbnails in different scales from one image using parameter "scales".
     * For example, if scales = [1,2], it will be created 2 thumbnails,
     * in size width x height and 2*width x 2*height.
     * 
     * Please note, if requested thumbnail has the same or bigger width or height
     * than original image, it won't be created.
     * 
     * @return ImageImmutable[] As indexes are used the scales.
     */
    public function createThumbnails(
        Image $originalImage,
        ImageSize $thumbnailSize,
        array $scales = [1]
    ): array {
        /** @var ImageImmutable[] $thumbnails **/
        $thumbnails = [];
        foreach ($scales as $scale) {
            $thumbnail = $this->createThumbnailForScale($originalImage, $thumbnailSize, $scale);
            if ($thumbnail) {
                $thumbnails[$scale] = $thumbnail;
            }
        }
        return $thumbnails;
    }
    
    private function createThumbnailForScale(
        Image $originalImage,
        ImageSize $thumbnailSize,
        float $scale
    ): ?ImageImmutable {
        $originalSize = $originalImage->getSize(); 
        $scaledThumbnailSize = $thumbnailSize->scale($scale);
        if (!$scaledThumbnailSize->lessThan($originalSize)) {
            return null;
        } 
        $originalImageArea = $this->resizeStrategy->originalImageArea($originalSize, $scaledThumbnailSize);
        $realThumbnailSize = $this->resizeStrategy->realThumbnailSize($originalSize, $scaledThumbnailSize);
        return $originalImage->cropAndResize(
            $originalImageArea->x,
            $originalImageArea->y,
            $originalImageArea->width,
            $originalImageArea->height,
            $realThumbnailSize->width,
            $realThumbnailSize->height
        );
    }
}
