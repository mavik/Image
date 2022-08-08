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

/**
 * Immutable version of Image
 */
class ImageImmutable extends Image
{
    private $flagCloneResourceWhenCloning = true;

    public function resize(int $width, int $height): ImageImmutable
    {
        $newResource = $this->getGraphicLibrary()->resize($this->getResource(), $width, $height, true);
        return $this->cloneWithNewResource($newResource);
    }

    public function crop(int $x, int $y, int $width, int $height): ImageImmutable
    {
        $newResource = $this->getGraphicLibrary()->crop($this->getResource(), $x, $y, $width, $height, true);
        return $this->cloneWithNewResource($newResource);
    }

    public function cropAndResize(
        int $x,
        int $y,
        int $width,
        int $height,
        int $toWidth,
        int $toHeight
    ) {
        $newResource = $this->getGraphicLibrary()->cropAndResize($this->getResource(), $x, $y, $width, $height, $toWidth, $toHeight, true);
        return $this->cloneWithNewResource($newResource);
    }

    private function cloneWithNewResource($resource): ImageImmutable
    {
        $this->flagCloneResourceWhenCloning = false;
        $newImage = clone $this;
        $this->flagCloneResourceWhenCloning = true;
        $newImage->resource = $resource;
        return $newImage;
    }
    
    public function __clone()
    {
        if (isset($this->file)) {
            $this->file = clone $this->file;
        }
        if (isset($this->graphicLibrary)) {
            $this->graphicLibrary = clone $this->graphicLibrary;
        }
        if ($this->flagCloneResourceWhenCloning && isset($this->resource)) {
            $this->resource = $this->graphicLibrary->clone($this->resource);
        }
    }
}