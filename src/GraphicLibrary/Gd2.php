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

class Gd2 implements GraphicLibraryInterface
{
    const DEFAULT_CONFIGURATION = [
        'jpg_quality' => 95,
        'png_compression' => 9,
        'webp_quality' => 95,
    ];

    private $configuration = [];

    public function __constuct(array $configuration)
    {
        $this->configuration = array_merge(self::DEFAULT_CONFIGURATION, $configuration);
    }

    public static function isInstalled(): bool
    {
        return function_exists('imagecreatetruecolor');
    }
    
    public function save($resource, string $path, int $type)
    {
        switch ($type) {
            case IMG_JPG:
                $result = imagejpeg($resource, $path, $this->configuration['jpg_quality']);
                break;
            case IMG_PNG:
                $result = imagepng($image, $path, $type);
                break;
            case IMG_GIF:
                $result = imagegif($resource, $path);
                break;
            case IMG_WEBP:
                $result = imagewebp($resource, $path, $this->configuration['webp_quality']);
            default :
                $result = false;
        }        
        if (!$result) {
            throw new GraphicLibraryException("Can't write image with type '{$type}' to file '{$path}'");
        }
    }

}
