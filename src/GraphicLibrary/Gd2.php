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
    
    /** @var int IMAGETYPE_XXX */
    private $imageType;

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
        $this->imageType = $type;
        switch ($type)
        {
            case IMAGETYPE_JPEG:
                $resource = imagecreatefromjpeg($src);
                break;
            case IMAGETYPE_PNG:
                $resource = imagecreatefrompng($src);
                break;
            case IMAGETYPE_GIF:
                $resource = imagecreatefromgif($src);
                break;
            case IMAGETYPE_WEBP:
                $resource = imagecreatefromwebp($src);
                break;
            default:
                throw new GraphicLibraryException('Trying to open unsupported type of image ' . image_type_to_mime_type($type));
        }
        if (!is_resource($resource)) {
            throw new GraphicLibraryException("Cannot open image \"{$src}\"");
        }
        return $resource;
    }

    public function close($resource)
    {
        imagedestroy($resource);
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
        if ($this->imageType == IMAGETYPE_JPEG || $this->imageType == IMAGETYPE_WBMP) {
            $newResource = imagecrop($resource, [
                'x' => $x,
                'y' => $y,
                'width' => $width,
                'height' => $height
            ]);            
            imagedestroy($resource);
            return $newResource;
        } else {        
            return $this->cropAndResize($resource, $x, $y, $width, $height, $width, $height);
        }        
    }
    
    /**
     * @param resource $resource
     * @param int $widht
     * @param int $height
     * @return resource
     */
    public function resize($resource, int $width, int $height)
    {
        return $this->cropAndResize($resource, 0, 0, imagesx($resource), imagesy($resource), $width, $height);
    }

    /**
     * @param resource $resource
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param int $toWidth
     * @param int $toHeight
     * @return resource
     */
    public function cropAndResize($resource, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight)
    {
        if (imagecolorstotal($resource)) {
            return $this->cropAndResizeIndexedColors($resource, $x, $y, $width, $height, $toWidth, $toHeight);
        } else {
            return $this->cropAndResizeTrueColors($resource, $x, $y, $width, $height, $toWidth, $toHeight);
        }
    }
    
    /**
     * @param resource $resource
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param int $toWidth
     * @param int $toHeight
     * @return resource
     */
    private function cropAndResizeTrueColors($resource, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight)
    {
        $newResource = imagecreatetruecolor($toWidth, $toHeight);
        if ($this->imageType != IMAGETYPE_JPEG) {
            imagealphablending($newResource, false);
            imagesavealpha($newResource, true);
            $transparent = imagecolorallocatealpha($newResource, 255, 255, 255, 127);
            imagefilledrectangle($newResource, 0, 0, $width, $height, $transparent);
        }
        imagecopyresampled($newResource, $resource, 0, 0, $x, $y, $toWidth, $toHeight, $width, $height);
        imagedestroy($resource);
        return $newResource;
    }
    
    /**
     * @param resource $resource
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param int $toWidth
     * @param int $toHeight
     * @return resource
     */
    private function cropAndResizeIndexedColors($resource, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight)
    {
        $newResource = imagecreatetruecolor($toWidth, $toHeight);
        
        $transparentIndex = imagecolortransparent($resource);
        if ($transparentIndex >= 0) {
            $transparentRgb = imagecolorsforindex($resource, $transparentIndex);
            $newTransparentIndex = imagecolorexact($newResource, $transparentRgb['red'], $transparentRgb['green'], $transparentRgb['blue']);
            imagefilledrectangle($newResource, 0, 0, $width, $height, $newTransparentIndex);
            imagecolortransparent($newResource, $newTransparentIndex);
        }
        
        imagecopyresized($newResource, $resource, 0, 0, $x, $y, $toWidth, $toHeight, $width, $height);
        
        $colorNumbers = imagecolorstotal($resource);
        imagetruecolortopalette($newResource, false, $colorNumbers);
        
        imagedestroy($resource);
        return $newResource;        
    }
}