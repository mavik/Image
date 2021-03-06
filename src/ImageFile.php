<?php
/**
 * PHP Library for Image processing and creating thumbnails
 *
 * @package Mavik\Image
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2021 Vitalii Marenkov
 * @license MIT; see LICENSE
 */

namespace Mavik\Image;

use Mavik\Image\Exception\FileException;

class ImageFile
{
    /** @var string */
    private $path;
    
    /** @var string */
    private $url;

    /** @var int */
    private $fileSize;
    
    /** @var int */
    private $width;

    /** @var int */
    private $height;
    
    /**
     * IMAGETYPE_XXX
     * 
     * @var int
     */
    private $type;
    
    public function __construct(string $url= null, string $path = null)
    {
        if(empty($url) && empty($path)) {
            throw new FileException('URL and path can\'n by empty at the same time.');
        }
        $this->path = $path;
        $this->url = $url;
    }
    
    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getFileSize(): int
    {
        if (!isset($this->fileSize)) {
            $this->initFileSize();
        }       
        return $this->fileSize;
    }
    
    /**
     * @return int IMAGETYPE_XXX
     */
    public function getType(): int
    {
        if (!isset($this->type)) {
            $this->initImageInfo();
        }
        return $this->type;
    }
    
    public function getWidth(): int
    {
        if (!isset($this->width)) {
            $this->initImageInfo();
        }
        return $this->width;
    }
    
    public function getHeight(): int
    {
        if (!isset($this->height)) {
            $this->initImageInfo();
        }
        return $this->height;
    }

    private function isUrl(string $file): bool
    {
        return preg_match('/^\w+\:\/\//', $file);
    }
    
    private function initImageInfo()
    {
        if ($this->path) {
            list(
                $this->width,
                $this->height,
                $this->type
            ) = getimagesize($this->path);
        } else {
            $this->initImageInfoFromUrl();
        }
    }

    private function initFileSize(): void
    {
        if ($this->path) {
            $this->initFileSizeFromPath();
        } else {
            $this->initImageInfoFromUrl();
        }
    }

    private function initFileSizeFromPath(): void
    {
        $this->fileSize = filesize($this->path);
        if ($this->fileSize === false) {
            throw new FileException();
        }        
    }
    
    /**
     * Set type, width, height and size of file
     * 
     * @return void
     * @throws FileException
     */
    private function initImageInfoFromUrl(): void
    {
        $context = stream_context_create([
            'http' => [
                'header' => 'Range: bytes=0-65536',
            ]
        ]);        
        $imageData = @file_get_contents($this->url, false, $context, 0, 65536);
        if ($imageData === false) {
            throw new FileException("Can't open URL \"{$this->url}\"");
        }        
        // $http_response_header is setted by PHP in file_get_contents()
        $httpHeaders = $this->parseHttpHeaders($http_response_header);
        $this->fileSize = $this->fileSizeFromHttpHeaders($httpHeaders);
        if (!isset($this->fileSize)) {
            $imageData = file_get_contents($this->url);
            $this->fileSize = strlen($imageData);
        }
        $imageSize = getimagesizefromstring($imageData);
        if (!isset($imageSize[0]) || !isset($imageSize[1]) || !isset($imageSize[2])) {
            throw new FileException("Can't get size or type of image \"{$this->url}\"");
        }
        $this->width = $imageSize[0];
        $this->height = $imageSize[1];
        $this->type = $imageSize[2];
    }
    
    private function fileSizeFromHttpHeaders(array $httpHeaders = null): ?int
    {        
        if (!isset($httpHeaders['response_code'])) {
            return null;
        }
        if (
            $httpHeaders['response_code'] == 206 &&
            isset($httpHeaders['content-range']) &&
            strpos($httpHeaders['content-range'], 'bytes') !== false
        ) {
            $parts = explode('/', $httpHeaders['content-range']);
            return (int)$parts[1] ?? null;            
        }
        if (
            $httpHeaders['response_code'] == 200 &&
            isset($httpHeaders['content-length']) &&
            is_numeric($httpHeaders['content-length'])
        ) {
            return (int)$httpHeaders['content-length'];
        }
        return null;
    }

    private function parseHttpHeaders(array $httpHeaders = null): array
    {
        $result = [];
        if (!is_array($httpHeaders)) {
            return $result;
        }
        foreach ($httpHeaders as $line) {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) {
                $result[strtolower(trim($parts[0]))] = trim($parts[1]);
            } else {
                $result[] = $line;
                if (preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#',$line, $matches)) {
                    $result['response_code'] = intval($matches[1]);
                }
            }
        }
        return $result;
    }    
}
