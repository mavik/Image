<?php
/**
 * PHP Library for Image processing and creating thumbnails
 *
 * @package Mavik\Image
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2021 Vitalii Marenkov
 * @license MIT; see LICENSE
 */
namespace Mavik\Image;

use Mavik\Image\ThumbnailsMaker\ResizeStrategyFactory;
use Mavik\Image\ThumbnailsMaker;

class ImageWithThumbnails extends ImageImmutable
{
    /** @var ImageImmutable[] */
    private $thumbnails = [];

    public static function create(
        string $src,
        ImageSize $thumbnailSize = null,
        string $resizeType = 'stretch',
        array $thumbnailScails = [1]
    ): self {
        $image = parent::create($src);
        if ($thumbnailSize) {
            self::addThumbnails($image, $thumbnailSize, $resizeType, $thumbnailScails);
        }
        return $image;
    }

    public static function createFromString(
        string $content,
        ImageSize $thumbnailSize = null,
        string $resizeType = 'stretch',
        array $thumbnailScails = [1]
    ): self {
        $image = parent::createFromString($content);
        if ($thumbnailSize) {
            self::addThumbnails($image, $thumbnailSize, $resizeType, $thumbnailScails);
        }
        return $image;        
    }
    
    /**
     * @return ImageImmutable[]
     */
    public function getThumbnails(): array 
    {
        return $this->thumbnails;
    }

    private static function addThumbnails(
        self $image,
        ImageSize $thumbnailSize,
        string $resizeType,
        array $thumbnailScails
    ): void {
        $resizeStrategy = ResizeStrategyFactory::create($resizeType);
        $thumbnailsMaker = new ThumbnailsMaker($resizeStrategy);
        $image->thumbnails = $thumbnailsMaker->createThumbnails($image, $thumbnailSize, $thumbnailScails);
    }
}
