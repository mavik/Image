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

    public function __constuct(array $configuration);

    public static function isInstalled(): bool;

    public function save($resource, string $path, string $type): void;
}
