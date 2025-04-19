<?php
declare(strict_types=1);

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
use Mavik\Image\ThumbnailsMaker\ResizeStrategyInterface;
use Mavik\Plugin\Content\Thumbnails\Extension\Thumbnails;

class ImageWithThumbnails extends ImageImmutable
{
    /** @var ImageImmutable[] */
    private $thumbnails = [];

    public static function create(
        string $src,
        Configuration $configuration,
        ImageSize $thumbnailSize = null,
        ResizeStrategyInterface $resizeStrategy = null,
        ThumbnailsMaker $thumbnailsMaker = null,
        string $thumbnailsDir = 'thumbnails',
        array $thumbnailScails = [1]
    ): static {
        $image = parent::create($src, $configuration);
        if ($thumbnailSize && $resizeStrategy && $thumbnailsMaker) {
            $image->thumbnails = $thumbnailsMaker->createThumbnails(
                $image,
                $thumbnailSize,
                $resizeStrategy,
                $thumbnailsDir,
                $thumbnailScails
            );
        }
        return $image;
    }

    public static function createFromString(
        string $content,
        Configuration $configuration,
        ImageSize $thumbnailSize = null,
        ResizeStrategyInterface $resizeStrategy = null,
        ThumbnailsMaker $thumbnailsMaker = null,
        string $thumbnailsDir = 'thumbnails',
        array $thumbnailScails = [1]
    ): static {
        $image = parent::createFromString($content, $configuration);
        if ($thumbnailSize) {
            $image->thumbnails = $thumbnailsMaker->createThumbnails(
                $image,
                $thumbnailSize,
                $resizeStrategy,
                $thumbnailsDir,
                $thumbnailScails
            );
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
}
