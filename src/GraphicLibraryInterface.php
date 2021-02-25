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
     * Returns graphic resource or object
     * 
     * @param string $src
     * @param int $type IMAGETYPE_XXX
     * @return mix
     */
    public function open(string $src, int $type);

    /**
     * Save image to file
     * 
     * @param mix $resource Depends on graphic library
     * @param string $path
     * @param int $type IMAGETYPE_XXX
     * @return void
     */
    public function save($resource, string $path, int $type): void;
}
