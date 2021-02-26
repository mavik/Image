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

class GmagickTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $webRoot = __DIR__ . '/../../resources/images';
        HttpServer::start($webRoot);
    }    
        
    /**
     * @covers Mavik\Image\GraphicLibrary\Gmagick::open
     * @dataProvider imagesToOpen
     */
    public function testOpen(string $src, int $type)
    {
        $gmagick = new Gmagick();
        $resource = $gmagick->open($src, $type);
        $this->assertInstanceOf('Gmagick', $resource);
    }
    
    /**
     * @covers Mavik\Image\GraphicLibrary\Gmagick::save
     * @dataProvider imagesToSave
     */
    public function testSave(string $src, int $type)
    {
        $savedFile = __DIR__ . '/../../temp/' . basename($src);
        $gmagick = new Gmagick();
        $resource = $gmagick->open($src, $type);
        $gmagick->save($resource, $savedFile, $type);
        $this->assertLessThan(1, CompareImages::distance($savedFile, $src));
        unlink($savedFile);
    }

    public function testCrop()
    {
        $src = __DIR__ . '/../../resources/images/apple.jpg';
        $savedFile = __DIR__ . '/../../temp/' . basename($src);
        $expectedFile = __DIR__ . '/../../resources/images/apple-crop-25-40-800-900.jpg';
        
        $gmagick = new Gmagick();
        $resource = $gmagick->open($src, IMAGETYPE_JPEG);
        $resource = $gmagick->crop($resource, 25, 40, 800, 900);
        $gmagick->save($resource, $savedFile, IMAGETYPE_JPEG);
        
        $imageSize = getimagesize($savedFile);
        $this->assertEquals(800, $imageSize[0]);
        $this->assertEquals(900, $imageSize[1]);
        $this->assertEquals(IMAGETYPE_JPEG, $imageSize[2]);
        
        $this->assertLessThan(1, CompareImages::distance($expectedFile, $savedFile));
        unlink($savedFile);        
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
}
