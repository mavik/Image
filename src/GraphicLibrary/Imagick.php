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
use Mavik\Image\Exception\GraphicLibraryException;

class Imagick implements GraphicLibraryInterface
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
    
    /** @var int */
    private $type;

    public function __construct(array $configuration = [])
    {
        $this->configuration = array_merge(self::DEFAULT_CONFIGURATION, $configuration);
    }
    
    public static function isInstalled(): bool
    {
        return class_exists('Imagick');
    }
    
    /**
     * @param string $src
     * @param int $type IMAGETYPE_XXX
     * @return \Imagick
     */
    public function open(string $src, int $type)
    {
        $this->type = $type;
        return new \Imagick($src);
    }

    /**
     * @param \Imagick $resource
     * @param string $path
     * @param int $type IMAGETYPE_XXX
     * @return void
     * @throws GraphicLibraryException
     */
    public function save($resource, string $path, int $type): void
    {
        if(!$resource->setImageFormat(self::TYPES[$type])) {
            throw new GraphicLibraryException('Format ' . self::TYPES[$type] . ' is not supported be Imagick.');
        }
        $resource->writeImage($path);
    }
    
    /**
     * @param \Imagick $resource
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return \Imagick
     */
    public function crop($resource, int $x, int $y, int $width, int $height)
    {
        $resource->cropImage($width, $height, $x, $y);        
        /** Fix incorrect size of cropped gif */
        if ($this->type == IMAGETYPE_GIF) {
            $resource->setImagePage($width, $height, 0, 0);
        }        
        return $resource;
    }

    /**
     * @param \Imagick $resource
     * @param int $width
     * @param int $height
     * @return \Imagick
     * @throws GraphicLibraryException
     */
    public function resize($resource, int $width, int $height)
    {
        if (!$resource->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1)) {
            throw new GraphicLibraryException("Imagick: Image cannot be resized.");
        }
        return $resource;
    }
    
    /**
     * @param \Imagick $image
     * @return \Imagick
     */
    public function cropAndResize($image, $x, $y, $width, $height, $toWidth, $toHeight)
    {
        $cropedImage = $this->crop($image, $x, $y, $width, $height);
        return $this->resize($cropedImage, $toWidth, $toHeight);
    }

}
