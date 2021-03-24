<?php
/* 
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2021 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */

namespace Mavik\Image\Tests\Unit\GraphicLibrary;

use PHPUnit\Framework\TestCase;
use Mavik\Image\Tests\HttpServer;
use Mavik\Image\Tests\CompareImages;
use Mavik\Image\GraphicLibraryInterface;

abstract class AbstractTest extends TestCase
{
    /** @var GraphicLibraryInterface */
    private $instance;
    
    public static function setUpBeforeClass(): void
    {
        $webRoot = __DIR__ . '/../../resources/images';
        HttpServer::start($webRoot);
    }
    
    public function setUp(): void
    {
        $this->instance = $this->newInstance();
    }

    public function testOpen(string $src, int $imgType)
    {        
        $resource = $this->instance->open($src, $imgType);
        $this->verifyResource($resource);
    }
    
    public function testSave(string $src, int $imgType)
    {
        $savedFile = __DIR__ . '/../../temp/' . basename($src);
        $resource = $this->instance->open($src, $imgType);
        $this->instance->save($resource, $savedFile, $imgType);
        $this->assertLessThan(1, CompareImages::distance($savedFile, $src));
        unlink($savedFile);
    }

    public function testCrop(int $imgType, int $x, int $y, int $width, int $height, string $src, string $expectedFile)
    {
        $savedFile = __DIR__ . '/../../temp/' . basename($src);
        
        $image = $this->instance->open($src, $imgType);
        $cropedImage = $this->instance->crop($image, $x, $y, $width, $height);
        $this->instance->save($cropedImage, $savedFile, $imgType);
        
        $imageSize = getimagesize($savedFile);
        $this->assertEquals($width, $imageSize[0]);
        $this->assertEquals($height, $imageSize[1]);
        $this->assertEquals($imgType, $imageSize[2]);
        
        $this->assertLessThan(1, CompareImages::distance($expectedFile, $savedFile));
        unlink($savedFile);
    }
    
    public function testResize(int $imgType, int $width, int $height, string $src, string $expectedFile)
    {
        $savedFile = __DIR__ . '/../../temp/' . basename($src);

        $image = $this->instance->open($src, $imgType);
        $resizedImage = $this->instance->resize($image, $width, $height);
        $this->instance->save($resizedImage, $savedFile, $imgType);
        
        $imageSize = getimagesize($savedFile);
        $this->assertEquals($width, $imageSize[0]);
        $this->assertEquals($height, $imageSize[1]);
        $this->assertEquals($imgType, $imageSize[2]);
        
        $this->assertLessThan(3, CompareImages::distance($expectedFile, $savedFile));
        unlink($savedFile);
    }    

    public function testCropAndResize(int $imgType, int $x, int $y, int $width, int $height, int $toWidth, int $toHeight, string $src, string $expectedFile)
    {
        $savedFile = __DIR__ . '/../../temp/' . basename($src);
        
        $image = $this->instance->open($src, $imgType);
        $cropedImage = $this->instance->cropAndResize($image, $x, $y, $width, $height, $toWidth, $toHeight);
        $this->instance->save($cropedImage, $savedFile, $imgType);
        
        $imageSize = getimagesize($savedFile);
        $this->assertEquals($toWidth, $imageSize[0]);
        $this->assertEquals($toHeight, $imageSize[1]);
        $this->assertEquals($imgType, $imageSize[2]);
        
        $this->assertLessThan(1, CompareImages::distance($expectedFile, $savedFile));
        unlink($savedFile);
    }
    
    abstract protected function newInstance(): GraphicLibraryInterface;
    
    /**
     * @param mix $resource Type depends on using Graphic Library
     */
    abstract protected function verifyResource($resource): void;
}