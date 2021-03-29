<?php
/**
 * PHP Library for Image processing and creating thumbnails
 *
 * @package Mavik\Image
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2021 Vitalii Marenkov
 * @license MIT; see LICENSE
 */
namespace Mavik\Image;

interface GraphicLibraryInterface
{
    public function __construct(array $configuration = []);

    public static function isInstalled(): bool;

    /**
     * Returns image - resource or object
     * 
     * @param string $src Url or path to image
     * @param int $type IMAGETYPE_XXX
     * @return mix Type depends on graphic library
     */
    public function open(string $src, int $type);

    /**
     * Save image to file
     * 
     * @param mix $image Type depends on graphic library
     * @param string $path
     * @param int $type IMAGETYPE_XXX
     * @return void
     */
    public function save($image, string $path, int $type): void;
    
    /**
     * Free resources
     * 
     * @param mix $image Type depends on graphic library
     */
    public function close($image): void;

    /**
     * @param mix $image Type depends on graphic library
     * @return mix Image - resource or object, it depends on graphic library
     */
    public function crop($image, int $x, int $y, int $width, int $height);

    /**
     * @param mix $image Depends on graphic library
     * @return mix Image - resource or object, it depends on graphic library
     */
    public function resize(
        $image, 
        int $width, 
        int $height
    );
    
    /**
     * It can be used for creating thumbnails.
     * 
     * Some library can do it as one operation,
     * and we don't want use two operations with these libraries
     * 
     * @param mix $image Type depends on graphic library
     * @return mix Image - resource or object, it depends on graphic library
     */
    public function cropAndResize($image, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight);
}
