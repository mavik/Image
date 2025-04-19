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

/**
 * Facade of the library
 */
class ImageFactory
{
    /** @var Configuration */
    private $configuration;

    /** @var ThumbnailsMaker */
    private $thumbnailsMaker;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function create(string $src): Image
    {
        return Image::create($src, $this->configuration);
    }
    
    public function createFromString(string $src): Image
    {
        return Image::createFromString($src, $this->configuration);
    }
    
    public function createImmutable(string $src): ImageImmutable
    {
        return ImageImmutable::create($src, $this->configuration);
    }
    
    public function createImmutableFromString(string $src): ImageImmutable
    {
        return ImageImmutable::createFromString($src, $this->configuration);
    }
    
    public function createImageWithThumbnails(
        string $src,
        int $width = null,
        int $height = null,
        string $resizeType = 'stretch',
        string $thumbnailsDir = 'thumbnails',
        array $thumbnailScails = [1]
    ): ImageWithThumbnails {
        return ImageWithThumbnails::create(
            $src,
            $this->configuration,
            new ImageSize($width, $height),
            ResizeStrategyFactory::create($resizeType),
            $this->thumbnailsMaker(),
            $thumbnailsDir,
            $thumbnailScails,
        );
    }
    
    public function createImageWithThumbnailsFromString(
        string $content,
        int $width = null,
        int $height = null,
        string $resizeType = 'stretch',
        string $thumbnailsDir = 'thumbnails',
        array $thumbnailScails = [1]
    ): ImageWithThumbnails {
        return ImageWithThumbnails::createFromString(
            $content,
            $this->configuration,
            new ImageSize($width, $height),
            ResizeStrategyFactory::create($resizeType),
            $this->thumbnailsMaker(),
            $thumbnailsDir,
            $thumbnailScails,
        );
    }

    private function thumbnailsMaker(): ThumbnailsMaker
    {
        if (!isset($this->thumbnailsMaker)) {
            $this->thumbnailsMaker = new ThumbnailsMaker();
        }
        return $this->thumbnailsMaker;
    }
}
