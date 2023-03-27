<?php
/*
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2022 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */
namespace Mavik\Image\ThumbnailsMaker;

class ResizeStrategyFactory
{
    /**
     * @throws \InvalidArgumentException
     */
    public static function create(string $resizeType): ResizeStrategyInterface
    {
        $className = '\\Mavik\\Image\\ThumbnailsMaker\\ResizeStrategy\\' . ucfirst(strtolower($resizeType));
        if (class_exists($className, true)) {
            return new $className;
        }
        throw new \InvalidArgumentException("Resize type \"{$resizeType}\" isn't supported.");
    }
}
