<?php
/*
 * PHP Library for Image processing and creating thumbnails
 *
 * @package Mavik\Image
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2021 Vitalii Marenkov
 * @license MIT; see LICENSE
*/

namespace Mavik\Image;

use PHPUnit\Framework\TestCase;
use Mavik\Image\Tests\HttpServer;
use Mavik\Image\Tests\CompareImages;

class ImageTest extends TestCase
{
    
    public static function setUpBeforeClass(): void
    {
        $webRoot = __DIR__ . '/../resources/images';
        HttpServer::start($webRoot);
        
        Image::configure([
            'base_url' => 'http://test.com/',
            'web_root_dir' => __DIR__ . '/../resources'
        ]);
    }
    
    /**
     * @covers Image::getType
     * @dataProvider files
     */
    public function testGetType(string $src, array $info)
    {
        $image = new Image($src);
        $this->assertEquals($info['type'], $image->getType());
    }

    /**
     * @covers Image::getWidth
     * @dataProvider files
     */
    public function testGetWidth(string $src, array $info)
    {
        $image = new Image($src);
        $this->assertEquals($info['width'], $image->getWidth());
    }
    
    /**
     * @covers Image::getHeight
     * @dataProvider files
     */
    public function testGetHeight(string $src, array $info)
    {
        $image = new Image($src);
        $this->assertEquals($info['height'], $image->getHeight());
    }    
    
    /**
     * @covers Image::getFileSize
     * @dataProvider files
     */
    public function testGetFileSize(string $src, array $info)
    {
        $image = new Image($src);
        $this->assertEquals($info['file_size'], $image->getFileSize());
    }
    
    /**
     * @covers Image::save
     * @dataProvider imagesToSave
     */
    public function testSave(string $origFile)
    {    
        $savedFile = __DIR__ . '/../temp/' . basename($origFile);
        $image = new Image($origFile);
        $image->save($savedFile);                        
        $this->assertLessThan(1, CompareImages::distance($origFile, $savedFile));
        unlink($savedFile);
    }
    
    public function imagesToSave()
    {
        return [
            0 => [__DIR__ . '/../resources/images/apple.jpg'],
            1 => [__DIR__ . '/../resources/images/butterfly_with_transparent_bg.png'],
            2 => [__DIR__ . '/../resources/images/snowman-pixel.gif'],
        ];
    }

    public function files()
    {
        return [
            [   0 =>
                __DIR__ . '/../resources/images/apple.jpg',
                [
                    'width'     => 1200,
                    'height'    => 1200,
                    'type'      => IMAGETYPE_JPEG,
                    'file_size' => 224643,
                ]
            ],[ 1 =>
                __DIR__ . '/../resources/images/butterfly_with_transparent_bg.png',
                [
                    'width'     => 1280,
                    'height'    => 1201,
                    'type'      => IMAGETYPE_PNG,
                    'file_size' => 308897,    
                ]            
            ],[ 2 =>
                __DIR__ . '/../resources/images/chrismas tree with transparent bg.png',
                [
                    'width'     => 1615,
                    'height'    => 1920,
                    'type'      => IMAGETYPE_PNG,
                    'file_size' => 141327,    
                ]
            ],[ 3 =>
                __DIR__ . '/../resources/images/pinapple-animated.gif',
                [
                    'width'     => 457,
                    'height'    => 480,
                    'type'      => IMAGETYPE_GIF,
                    'file_size' => 157012,
                ]
            ],[ 4 =>
                __DIR__ . '/../resources/images/snowman-pixel.gif',
                [
                    'width'     => 700,
                    'height'    => 1300,
                    'type'      => IMAGETYPE_GIF,
                    'file_size' => 53777,
                ]
            ],[ 5 =>
               __DIR__ . '/../resources/images/tree_with_white_background.jpg',
                [
                    'width'     => 1280,
                    'height'    => 1280,
                    'type'      => IMAGETYPE_JPEG,
                    'file_size' => 181304,
                ] 
            ],[ 6 =>
                __DIR__ . '/../resources/images/house.webp',
                [
                    'width'     => 1536,
                    'height'    => 1024,
                    'type'      => IMAGETYPE_WEBP,
                    'file_size' => 644986,
                ]
            ],[ 7 =>
                __DIR__ . '/../resources/images/beach.webp',
                [
                    'width'     => 730,
                    'height'    => 352,
                    'type'      => IMAGETYPE_WEBP,
                    'file_size' => 69622,
                ]
            ],[ 8 =>
                'http://localhost:8888/apple.jpg',
                [
                    'width'     => 1200,
                    'height'    => 1200,
                    'type'      => IMAGETYPE_JPEG,
                    'file_size' => 224643,    
                ]
            ],[ 9 =>
                'http://localhost:8888/beach.webp',
                [
                    'width'     => 730,
                    'height'    => 352,
                    'type'      => IMAGETYPE_WEBP,
                    'file_size' => 69622,
                ]
            ],[ 10 =>
                'https://upload.wikimedia.org/wikipedia/en/a/a7/Culinary_fruits_cropped_top_view.jpg',
                [
                    'width'     => 3224,
                    'height'    => 2145,
                    'type'      => IMAGETYPE_JPEG,
                    'file_size' => 2925171,
                ]
            ],[ 11 =>
                'https://pixnio.com/free-images/2020/01/24/2020-01-24-08-50-32-1200x800.jpg',
                [
                    'width'     => 1200,
                    'height'    => 800,
                    'type'      => IMAGETYPE_JPEG,
                    'file_size' => 169395,
                ]
            ]
        ];
    }
}