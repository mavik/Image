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

class Imagick implements GraphicLibraryInterface
{
    const DEFAULT_CONFIGURATION = [
        'jpg_quality' => 95,
        'png_compression' => 9,
        'webp_quality' => 95,
    ];
    
    const TYPES = [
        IMG_JPG => 'JPG',
        IMG_PNG => 'PNG',
        IMG_GIF => 'GIF',
        IMG_WEBP => 'WebP'
    ];    

    private $configuration = [];

    public function __constuct(array $configuration)
    {
        $this->configuration = array_merge(self::DEFAULT_CONFIGURATION, $configuration);
    }
    
    public static function isInstalled(): bool
    {
        return class_exists('Imagick');
    }
    
    public function save(\Imagick $resource, string $path, int $type): void
    {
        if(!$resource->setFormat(self::TYPES[$type])) {
            throw new GraphicLibraryException("Can't write image with type '{$type}' to file '{$path}'");
        }
        $resource->writeImage($path);
    }
    
}
