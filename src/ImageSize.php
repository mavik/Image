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
    /** @var int|null **/
    public $width;
    
    /** @var int|null **/
    public $height;    
    
    public function __construct(?int $width = null, ?int $height = null)
    {
        if (empty($width) && empty($height)) {
            throw new Exception('At least one parameter of ImageSize constructor has to be not null.');
        }
        $this->width = $width;
        $this->height = $height;
    }
}
