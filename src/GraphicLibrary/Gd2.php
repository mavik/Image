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
                $image = imagecreatefromjpeg($src);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($src);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($src);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($src);
                break;
            default:
                throw new GraphicLibraryException('Trying to open unsupported type of image ' . image_type_to_mime_type($type));
        }
        if (!is_resource($image)) {
            throw new GraphicLibraryException("Cannot open image \"{$src}\"");
        }
        return $image;
    }

    public function close($image)
    {
        imagedestroy($image);
    }
    
    /**
     * @param resource $image
     * @param string $path
     * @param int $type IMAGETYPE_XXX
     * @return void
     * @throws GraphicLibraryException
     */
    public function save($image, string $path, int $type): void
    {
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($image, $path, $this->configuration['jpg_quality']);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($image, $path, $type);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($image, $path);
                break;
            case IMAGETYPE_WEBP:
                $result = imagewebp($image, $path, $this->configuration['webp_quality']);
                break;
            default :
                throw new GraphicLibraryException('Trying to save unsupported type of image ' . image_type_to_mime_type($type));
        }        
        if (!$result) {
            throw new GraphicLibraryException("Can't write image with type '{$type}' to file '{$path}'");
        }
    }   
    
    /**
     * @param resource $image
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return resource
     */
    public function crop($image, int $x, int $y, int $width, int $height)
    {
        if ($this->imageType == IMAGETYPE_JPEG || $this->imageType == IMAGETYPE_WBMP) {
            $newResource = imagecrop($image, [
                'x' => $x,
                'y' => $y,
                'width' => $width,
                'height' => $height
            ]);            
            imagedestroy($image);
            return $newResource;
        } else {        
            return $this->cropAndResize($image, $x, $y, $width, $height, $width, $height);
        }        
    }
    
    /**
     * @param resource $image
     * @param int $widht
     * @param int $height
     * @return resource
     */
    public function resize($image, int $width, int $height)
    {
        return $this->cropAndResize($image, 0, 0, imagesx($image), imagesy($image), $width, $height);
    }

    /**
     * @param resource $image
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param int $toWidth
     * @param int $toHeight
     * @return resource
     */
    public function cropAndResize($image, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight)
    {
        if (imagecolorstotal($image)) {
            return $this->cropAndResizeIndexedColors($image, $x, $y, $width, $height, $toWidth, $toHeight);
        } else {
            return $this->cropAndResizeTrueColors($image, $x, $y, $width, $height, $toWidth, $toHeight);
        }
    }
    
    /**
     * @param resource $image
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param int $toWidth
     * @param int $toHeight
     * @return resource
     */
    private function cropAndResizeTrueColors($image, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight)
    {
        $newResource = imagecreatetruecolor($toWidth, $toHeight);
        if ($this->imageType != IMAGETYPE_JPEG) {
            imagealphablending($newResource, false);
            imagesavealpha($newResource, true);
            $transparent = imagecolorallocatealpha($newResource, 255, 255, 255, 127);
            imagefilledrectangle($newResource, 0, 0, $width, $height, $transparent);
        }
        imagecopyresampled($newResource, $image, 0, 0, $x, $y, $toWidth, $toHeight, $width, $height);
        imagedestroy($image);
        return $newResource;
    }
    
    /**
     * @param resource $image
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param int $toWidth
     * @param int $toHeight
     * @return resource
     */
    private function cropAndResizeIndexedColors($image, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight)
    {
        $newResource = imagecreatetruecolor($toWidth, $toHeight);
        
        $transparentIndex = imagecolortransparent($image);
        if ($transparentIndex >= 0) {
            $transparentRgb = imagecolorsforindex($image, $transparentIndex);
            $newTransparentIndex = imagecolorexact($newResource, $transparentRgb['red'], $transparentRgb['green'], $transparentRgb['blue']);
            imagefilledrectangle($newResource, 0, 0, $width, $height, $newTransparentIndex);
            imagecolortransparent($newResource, $newTransparentIndex);
        }
        
        imagecopyresized($newResource, $image, 0, 0, $x, $y, $toWidth, $toHeight, $width, $height);
        
        $colorNumbers = imagecolorstotal($image);
        imagetruecolortopalette($newResource, false, $colorNumbers);
        
        imagedestroy($image);
        return $newResource;        
    }
}