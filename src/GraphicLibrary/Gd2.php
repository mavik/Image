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
                imagealphablending($image, false);
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

    public function close($image): void
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
                imageSaveAlpha($image, true);
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
    
    public function getHeight($image): int
    {
        return imagesy($image);
    }

    public function getWidth($image): int
    {
        return imagesx($image);
    }

    /**
     * @param resource $image
     * @return resource
     */
    public function crop($image, int $x, int $y, int $width, int $height, bool $immutable = false)
    {
        if ($this->imageType == IMAGETYPE_JPEG || $this->imageType == IMAGETYPE_WBMP) {
            $newImage = imagecrop($image, [
                'x' => $x,
                'y' => $y,
                'width' => $width,
                'height' => $height
            ]);                        
            if (!$immutable) {
                imagedestroy($image);
            }
            return $newImage;
        } else {
            // imagecrop works incorrect with indexed images with transparent
            return $this->cropAndResize($image, $x, $y, $width, $height, $width, $height, $immutable);
        }        
    }
    
    /**
     * @param resource $image
     * @return resource
     */
    public function resize($image, int $width, int $height, bool $immutable = false)
    {
        return $this->cropAndResize($image, 0, 0, imagesx($image), imagesy($image), $width, $height, $immutable);
    }

    /**
     * @param resource $image
     * @return resource
     */
    public function cropAndResize($image, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight, bool $immutable = false)
    {
        if (imagecolorstotal($image)) {
            return $this->cropAndResizeIndexedColors($image, $x, $y, $width, $height, $toWidth, $toHeight, $immutable);
        } else {
            return $this->cropAndResizeTrueColors($image, $x, $y, $width, $height, $toWidth, $toHeight, $immutable);
        }
    }
    
    /**
     * @param resource $image
     * @return resource
     */
    private function cropAndResizeTrueColors($image, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight, bool $immutable)
    {
        $newImage = imagecreatetruecolor($toWidth, $toHeight);
        if ($this->imageType != IMAGETYPE_JPEG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
        }
        imagecopyresampled($newImage, $image, 0, 0, $x, $y, $toWidth, $toHeight, $width, $height);
        if (!$immutable) {
            imagedestroy($image);
        }
        return $newImage;
    }
    
    /**
     * @param resource $image
     * @return resource
     */
    private function cropAndResizeIndexedColors($image, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight, bool $immutable)
    {
        $newImage = imagecreatetruecolor($toWidth, $toHeight);
        
        $transparentIndex = imagecolortransparent($image);
        if ($transparentIndex >= 0) {
            $transparentRgb = imagecolorsforindex($image, $transparentIndex);
            $newTransparentIndex = imagecolorexact($newImage, $transparentRgb['red'], $transparentRgb['green'], $transparentRgb['blue']);
            imagefilledrectangle($newImage, 0, 0, $width, $height, $newTransparentIndex);
            imagecolortransparent($newImage, $newTransparentIndex);
        }
        
        imagecopyresized($newImage, $image, 0, 0, $x, $y, $toWidth, $toHeight, $width, $height);
        
        $colorNumbers = imagecolorstotal($image);
        imagetruecolortopalette($newImage, false, $colorNumbers);
        
        if (!$immutable) {
            imagedestroy($image);
        }
        return $newImage;        
    }
}