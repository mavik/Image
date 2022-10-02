<?php
/*
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2022 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */
namespace Mavik\Image;

class ImageSize
{
    /** @var int **/
    public $width;
    
    /** @var int **/
    public $height;    
    
    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }
}
