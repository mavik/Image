<?php
/*
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2021 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */
namespace Mavik\Image;

abstract class ResizeTypeAbstract 
{
    public function areaAndRealSize(int $fromWidth, int $fromHeight, int $toWidth, int $toHeight): array
    {
        list($toWidthReal, $toHeightReal) = $this->toSizeReal($fromWidth, $fromHeight, $toWidth, $toHeight);
        list($fromX1, $fromY1, $fromX2, $fromY2) = $this->area
        
    }
    
    /**
     * @param int $fromWidth
     * @param int $fromHeight
     * @param int $toWidth
     * @param int $toHeight
     * @return int[string] ['widht' => int, 'height' => int]
     */
    protected function toSizeReal(int $fromWidth, int $fromHeight, int $toWidth, int $toHeight): array
    {
        switch ($this->priorityDimension($fromWidth, $fromHeight, $toWidth, $toHeight)) {
            case 'w':
                $toWidthReal = $toWidth;
                $toHeightReal = round($fromHeight * $toWidth / $fromWidth);
                break;
            case 'h':
                $toHeightReal = $toHeight;
                $toWidthReal  = round($fromWidth * $toHeight / $fromHeight);
                break;
            default:
                $toWidthReal  = $toWidth;
                $toHeightReal = $toHeight;
        }        
        return [
            'width' => $toWidthReal,
            'height' => $toHeightReal
        ];
    }




    protected function priorityDimension(int $fromWidth, int $fromHeight, int $toWidth, int $toHeight): string
    {
        return '';
    }
    
    
}
