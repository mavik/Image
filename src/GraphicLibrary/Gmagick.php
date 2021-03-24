<?php
/**
 * PHP Library for Image processing and creating thumbnails
 *
 * @package Mavik\Image
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2021 Vitalii Marenkov
 * @license MIT; see LICENSE
 */
namespace Mavik\Image\GraphicLibrary;

use Mavik\Image\GraphicLibraryInterface;

class Gmagick implements GraphicLibraryInterface
{
    const DEFAULT_CONFIGURATION = [
        'jpg_quality' => 95,
        'png_compression' => 9,
        'webp_quality' => 95,
    ];
    
    const TYPES = [
        IMAGETYPE_JPEG => 'JPG',
        IMAGETYPE_PNG => 'PNG',
        IMAGETYPE_GIF => 'GIF',
        IMAGETYPE_WEBP => 'WebP'
    ];

    private $configuration = [];

    public function __construct(array $configuration = [])
    {
        $this->configuration = array_merge(self::DEFAULT_CONFIGURATION, $configuration);
    }
    
    public static function isInstalled(): bool
    {
        return class_exists('Gmagick');
    }
    
    /**
     * @param string $src
     * @param int $type IMAGETYPE_XXX
     * @return \Gmagick
     */
    public function open(string $src, int $type)
    {
        /**
         * We cannot use
         * return new \Gmagick($src);
         * because Gmagick doesn't support wrappers (https://, ftp:// itp.)
         * 
         * We cannot use
         * $fp = fopen($src, 'rb');
         * $image = new \Gmagick();
         * return $image->readimagefile($fp);
         * because it causes Segmentation fault.
         * 
         * Only that works correct.
         */ 
        $image = new \Gmagick();
        return $image->readimageblob(file_get_contents($src));
    }
    
    public function close(\Gmagick $resource)
    {
        unset($resource);
    }

    /**
     * @param \Gmagick $resource
     * @param string $path
     * @param int $type IMAGETYPE_XXX
     * @throws GraphicLibraryException
     */
    public function save($resource, string $path, int $type): void
    {
        if (!$resource->setimageformat(self::TYPES[$type])) {
            throw new GraphicLibraryException("Can't write image with type '{$type}' to file '{$path}'");
        }
        $resource->writeimage($path);
    }
    
    /**
     * @param \Gmagick $resource
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return \Gmagick
     */
    public function crop($resource, int $x, int $y, int $width, int $height): \Gmagick
    {
        $resource->cropImage($width, $height, $x, $y);
        return $resource;
    }
    
    /**
     * @param \Gmagick $resource
     * @return \Gmagick
     */
    public function resize($resource, int $width, int $height): \Gmagick
    {
        $resource->resizeimage($width, $height, \Gmagick::FILTER_TRIANGLE, 1);
        return $resource;
    }

    /**
     * @param \Gmagick $image
     * @return \Gmagick
     */
    public function cropAndResize($image, $x, $y, $width, $height, $toWidth, $toHeight)
    {
        $cropedImage = $this->crop($image, $x, $y, $width, $height);
        return $this->resize($cropedImage, $toWidth, $toHeight);
    }

    
}
