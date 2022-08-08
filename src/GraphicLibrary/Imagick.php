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
     * @param int $type IMAGETYPE_XXX
     * @return \Imagick
     */
    public function open(string $src, int $type)
    {
        $this->type = $type;
        return new \Imagick($src);
    }
    
    /**
     * @param \Imagick $image
     */
    public function close($image): void
    {
        unset($image);
    }

    /**
     * @param \Imagick $image
     * @param int $type IMAGETYPE_XXX
     * @throws GraphicLibraryException
     */
    public function save($image, string $path, int $type): void
    {
        if(!$image->setImageFormat(self::TYPES[$type])) {
            throw new GraphicLibraryException('Format ' . self::TYPES[$type] . ' is not supported be Imagick.');
        }
        $image->writeImage($path);
    }
    
    /**
     * @param \Imagick $image
     * @return \Imagick
     */    
    public function clone($image)
    {
       return clone $image; 
    }    
    
    /**
     * @param \Imagick $image
     */
    public function getHeight($image): int
    {
        return $image->getImageHeight();
    }

    /**
     * @param \Imagick $image
     */
    public function getWidth($image): int
    {
        return $image->getImageWidth();
    }    
 
    /**
     * @param \Imagick $image
     * @return \Imagick
     */
    public function crop($image, int $x, int $y, int $width, int $height, bool $immutable = false)
    {
        $tmpImage = $immutable ? clone $image : $image;
        $tmpImage->cropImage($width, $height, $x, $y);        
        /** Fix incorrect size of cropped gif */
        if ($this->type == IMAGETYPE_GIF) {
            $tmpImage->setImagePage($width, $height, 0, 0);
        }        
        return $tmpImage;
    }

    /**
     * @param \Imagick $image
     * @throws GraphicLibraryException
     */
    public function resize($image, int $width, int $height, bool $immutable = false)
    {
        $tmpImage = $immutable ? clone $image : $image;
        if (!$tmpImage->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1)) {
            throw new GraphicLibraryException("Imagick: Image cannot be resized.");
        }
        return $tmpImage;
    }
    
    /**
     * @param \Imagick $image
     * @return \Imagick
     */
    public function cropAndResize($image, $x, $y, $width, $height, $toWidth, $toHeight, bool $immutable = false)
    {
        $tmpImage = $immutable ? clone $image : $image;        
        $cropedImage = $this->crop($tmpImage, $x, $y, $width, $height);
        return $this->resize($cropedImage, $toWidth, $toHeight);
    }
}
