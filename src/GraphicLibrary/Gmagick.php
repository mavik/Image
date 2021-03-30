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
    
    public function close($image): void
    {
        unset($image);
    }

    /**
     * @param \Gmagick $image
     * @param string $path
     * @param int $type IMAGETYPE_XXX
     * @throws GraphicLibraryException
     */
    public function save($image, string $path, int $type): void
    {
        if (!$image->setimageformat(self::TYPES[$type])) {
            throw new GraphicLibraryException("Can't write image with type '{$type}' to file '{$path}'");
        }
        $image->writeimage($path);
    }
    
    /**
     * @param \Gmagick $image
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return \Gmagick
     */
    public function crop($image, int $x, int $y, int $width, int $height, bool $immutable = false): \Gmagick
    {
        $tmpImage = $immutable ? clone $image : $image;
        $tmpImage->cropImage($width, $height, $x, $y);
        return $tmpImage;
    }
    
    /**
     * @param \Gmagick $image
     * @return \Gmagick
     */
    public function resize($image, int $width, int $height, bool $immutable = false): \Gmagick
    {
        $tmpImage = $immutable ? clone $image : $image;
        $tmpImage->resizeimage($width, $height, \Gmagick::FILTER_TRIANGLE, 1);
        return $tmpImage;
    }

    /**
     * @param \Gmagick $image
     * @return \Gmagick
     */
    public function cropAndResize($image, $x, $y, $width, $height, $toWidth, $toHeight, bool $immutable = false)
    {
        $tmpImage = $immutable ? clone $image : $image;
        $cropedImage = $this->crop($tmpImage, $x, $y, $width, $height);
        return $this->resize($cropedImage, $toWidth, $toHeight);
    }
}
