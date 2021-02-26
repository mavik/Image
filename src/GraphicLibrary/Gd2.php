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

    public function __construct(array $configuration = [])
    {
        $this->configuration = array_merge(self::DEFAULT_CONFIGURATION, $configuration);
    }

    public static function isInstalled(): bool
    {
        return function_exists('imagecreatetruecolor');
    }
    
    /**
     * @param string $src
     * @param int $type IMAGETYPE_XXX
     * @return recource
     * @throws GraphicLibraryException
     */
    public function open(string $src, int $type)
    {
        switch ($type)
        {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($src);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($src);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($src);
            case IMAGETYPE_WEBP:
                return imagecreatefromwebp($src);
            default:
                throw new GraphicLibraryException('Trying to open unsupported type of image ' . image_type_to_mime_type($type));
        }
    }

    /**
     * @param resource $resource
     * @param string $path
     * @param int $type IMAGETYPE_XXX
     * @return void
     * @throws GraphicLibraryException
     */
    public function save($resource, string $path, int $type): void
    {
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($resource, $path, $this->configuration['jpg_quality']);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($resource, $path, $type);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($resource, $path);
                break;
            case IMAGETYPE_WEBP:
                $result = imagewebp($resource, $path, $this->configuration['webp_quality']);
                break;
            default :
                throw new GraphicLibraryException('Trying to save unsupported type of image ' . image_type_to_mime_type($type));
        }        
        if (!$result) {
            throw new GraphicLibraryException("Can't write image with type '{$type}' to file '{$path}'");
        }
    }   
    
    /**
     * @param resource $resource
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return resource
     */
    public function crop($resource, int $x, int $y, int $width, int $height)
    {
        $newResource = imagecrop($resource, [
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height
        ]);
        imagedestroy($resource);
        return $newResource;
    }
}
