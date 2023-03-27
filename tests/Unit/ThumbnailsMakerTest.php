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
use Mavik\Image\ThumbnailsMaker\ResizeStrategyInterface;
use Mavik\Image\ThumbnailsMaker\ImageArea;

class ThumbnailsMakerTest extends TestCase
{
    public function testCreateThumbnails()
    {
        $resizeStrategy = $this->createStub(ResizeStrategyInterface::class);
        $resizeStrategy->method('originalImageArea')->willReturn(new ImageArea(0, 0, 1600, 1200));
        $resizeStrategy->method('realThumbnailSize')->willReturnArgument(1);
        
        $image = $this->createMock(ImageImmutable::class);
        $image
            ->method('getSize')
            ->willReturn(new ImageSize(1600, 1200))
        ;
        $image
            ->expects($this->exactly(2))
            ->method('cropAndResize')
            ->withConsecutive(
                [$this->equalTo(0), $this->equalTo(0), $this->equalTo(1600), $this->equalTo(1200), $this->equalTo(200), $this->equalTo(150)],
                [$this->equalTo(0), $this->equalTo(0), $this->equalTo(1600), $this->equalTo(1200), $this->equalTo(400), $this->equalTo(300)]
            )
        ;
        
        $thumbnailsMaker = new ThumbnailsMaker($resizeStrategy);
        $thumbnails = $thumbnailsMaker->createThumbnails($image, new ImageSize(200, 150), [1,10,2]);
        
        $this->assertEquals(2, count($thumbnails));
        $this->assertArrayHasKey(1, $thumbnails);
        $this->assertArrayHasKey(2, $thumbnails);
        $this->assertArrayNotHasKey(0, $thumbnails);
        $this->assertArrayNotHasKey(10, $thumbnails);        
        $this->assertInstanceOf(ImageImmutable::class, $thumbnails[1]);
        $this->assertInstanceOf(ImageImmutable::class, $thumbnails[2]);
    }
}
