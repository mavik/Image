<?php
declare(strict_types=1);

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
     * @param ImageImmutable|Image $image Can be object of class ImageImmutable or Image, but ImageImmutable is recommended.
     * @return ImageImmutable[] As indexes are used the scales.
     */
    public function createThumbnails(
        Image $image,
        ImageSize $thumbnailSize,
        ResizeStrategyInterface $resizeStrategy,
        string $thumbnailsDir,
        array $scales = [1],
    ): array {
        /** @var ImageImmutable[] $thumbnails **/
        $thumbnails = [];
        foreach ($scales as $scale) {
            $thumbnail = $this->createThumbnailForScale($image, $thumbnailSize, $resizeStrategy, $thumbnailsDir, $scale);
            if ($thumbnail) {
                $thumbnails[$scale] = $thumbnail;
            }
        }
        return $thumbnails;
    }
    
    private function createThumbnailForScale(
        Image $image,
        ImageSize $thumbnailSize,
        ResizeStrategyInterface $resizeStrategy,
        string $thumbnailsDir,
        float $scale
    ): ?ImageImmutable {
        $originalSize = $image->getSize(); 
        $scaledThumbnailSize = $thumbnailSize->scale($scale);
        if (!$scaledThumbnailSize->lessThan($originalSize)) {
            return null;
        } 
        $originalImageArea = $resizeStrategy->originalImageArea($originalSize, $scaledThumbnailSize);
        $realThumbnailSize = $resizeStrategy->realThumbnailSize($originalSize, $scaledThumbnailSize);
        $thumbnail = ($image instanceof ImageImmutable ? $image : clone $image)
            ->cropAndResize(
                $originalImageArea->x,
                $originalImageArea->y,
                $originalImageArea->width,
                $originalImageArea->height,
                $realThumbnailSize->width,
                $realThumbnailSize->height
            )
        ;
        $this->saveThumbnail($thumbnail, $thumbnailsDir);
        return $thumbnail;
    }

    private function saveThumbnail(Image $thubnail, string $thumbnailsDir): void
    {
        $lastDotPosition = strrpos($thubnail->getPath(), '.') ?: strlen($thubnail->getPath());
        $newPath = 
            $thumbnailsDir
            . '/' . substr($thubnail->getPath(), 0, $lastDotPosition) 
            . '-' . $thubnail->getWidth() . 'x' . $thubnail->getHeight() 
            . '.' . substr($thubnail->getPath(), $lastDotPosition + 1)
        ;
        $thubnail->save($newPath, true);
    }
 }