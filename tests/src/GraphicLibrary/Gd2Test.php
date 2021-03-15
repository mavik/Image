<?php
/* 
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2021 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */

namespace Mavik\Image\GraphicLibrary;

use PHPUnit\Framework\TestCase;
use Mavik\Image\Tests\HttpServer;
use Mavik\Image\Tests\CompareImages;

class Gd2Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $webRoot = __DIR__ . '/../../resources/images';
        HttpServer::start($webRoot);
    }    
        
    /**
     * @covers Mavik\Image\GraphicLibrary\Gd2::open
     * @dataProvider imagesToOpen
     */
    public function testOpen(string $src, int $type)
    {
        $gd2 = new Gd2();
        $resource = $gd2->open($src, $type);
        $this->assertIsResource($resource);
    }
    
    /**
     * @covers Mavik\Image\GraphicLibrary\Gd2::save
     * @dataProvider imagesToSave
     */
    public function testSave(string $src, int $type)
    {
        $savedFile = __DIR__ . '/../../temp/' . basename($src);
        $gd2 = new Gd2();
        $resource = $gd2->open($src, $type);
        $gd2->save($resource, $savedFile, $type);
        $this->assertLessThan(1, CompareImages::distance($savedFile, $src));
        unlink($savedFile);
    }
    
    public function testCrop()
    {
        $src = __DIR__ . '/../../resources/images/apple.jpg';
        $savedFile = __DIR__ . '/../../temp/' . basename($src);
        $expectedFile = __DIR__ . '/../../resources/images/apple-crop-25-40-800-900.jpg';
        
        $gd2 = new Gd2();
        $resource = $gd2->open($src, IMAGETYPE_JPEG);
        $resource = $gd2->crop($resource, 25, 40, 800, 900);
        $gd2->save($resource, $savedFile, IMAGETYPE_JPEG);
        
        $imageSize = getimagesize($savedFile);
        $this->assertEquals(800, $imageSize[0]);
        $this->assertEquals(900, $imageSize[1]);
        $this->assertEquals(IMAGETYPE_JPEG, $imageSize[2]);
        
        $this->assertLessThan(1, CompareImages::distance($expectedFile, $savedFile));
        unlink($savedFile);
    }
    
    /**
     * @covers Mavik\Image\GraphicLibrary\Gd2::resize
     * @dataProvider imagesToResize
     */
    public function testResize(int $imgType, int $width, int $height, string $src, string $expectedFile)
    {
        $savedFile = __DIR__ . '/../../temp/' . basename($src);

        $gd2 = new Gd2();
        $resource = $gd2->open($src, $imgType);
        $resource = $gd2->resize($resource, $width, $height);
        $gd2->save($resource, $savedFile, $imgType);
        
        $imageSize = getimagesize($savedFile);
        $this->assertEquals($width, $imageSize[0]);
        $this->assertEquals($height, $imageSize[1]);
        $this->assertEquals($imgType, $imageSize[2]);
        
        $this->assertLessThan(2, CompareImages::distance($expectedFile, $savedFile));
        //unlink($savedFile);
    }

    public function imagesToOpen()
    {
        return [
            0 => [__DIR__ . '/../../resources/images/apple.jpg', IMAGETYPE_JPEG],
            1 => [__DIR__ . '/../../resources/images/butterfly_with_transparent_bg.png', IMAGETYPE_PNG],
            2 => [__DIR__ . '/../../resources/images/snowman-pixel.gif', IMAGETYPE_GIF],
            3 => [__DIR__ . '/../../resources/images/house.webp', IMAGETYPE_WEBP],
            4 => ['http://localhost:8888/apple.jpg', IMAGETYPE_JPEG],
            5 => ['https://upload.wikimedia.org/wikipedia/en/a/a7/Culinary_fruits_cropped_top_view.jpg', IMAGETYPE_JPEG],
        ];
    }
    
    public function imagesToSave()
    {
        return [
            0 => [__DIR__ . '/../../resources/images/apple.jpg', IMAGETYPE_JPEG],
            1 => [__DIR__ . '/../../resources/images/butterfly_with_transparent_bg.png', IMAGETYPE_PNG],
            2 => [__DIR__ . '/../../resources/images/snowman-pixel.gif', IMAGETYPE_GIF],
            3 => [__DIR__ . '/../../resources/images/house.webp', IMAGETYPE_WEBP],                        
        ];
    }
    
    public function imagesToResize()
    {
        return [
            0 => [
                IMAGETYPE_JPEG, 400, 500,
                __DIR__ . '/../../resources/images/apple.jpg',
                __DIR__ . '/../../resources/images/resized/apple-400-500.jpg'
            ],
            1 => [ 
                IMAGETYPE_PNG, 300, 281,
                __DIR__ . '/../../resources/images/butterfly_with_transparent_bg.png',
                __DIR__ . '/../../resources/images/resized/butterfly_with_transparent_bg-300-281.png'
            ],
            2 => [
                IMAGETYPE_GIF, 200, 226,
                __DIR__ . '/../../resources/images/bee.gif',
                __DIR__ . '/../../resources/images/resized/bee-200-226.gif'
            ],
            3 => [ 
                IMAGETYPE_GIF, 300, 281,
                __DIR__ . '/../../resources/images/butterfly_with_transparent_bg.gif',
                __DIR__ . '/../../resources/images/resized/butterfly_with_transparent_bg-300-281.gif'
            ],
            4 => [
                IMAGETYPE_WEBP, 300, 281,
                __DIR__ . '/../../resources/images/butterfly_with_transparent_bg.webp',
                __DIR__ . '/../../resources/images/resized/butterfly_with_transparent_bg.webp'
            ],
        ];
    }
}
