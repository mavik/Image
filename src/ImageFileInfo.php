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

/**
 * Get information about image file
 */
class ImageFileInfo
{
    /**
     * Get size and type of image and size of file
     * 
     * Returns array(
     *  'width'    => <width>,
     *  'height'   => <height>,
     *  'type'     => <constant IMG_XXX>,
     *  'file_size' => <size of file in bytes>,
     * )
     * 
     * @param Image $file
     * @return array
     */
    public function imageInfo(File $file): array
    {
        if ($file->getPath()) {
            $imageSize = $this->imageSizeFromFile($file->getPath());
            $fileSize = filesize($file->getPath());
        } else {
            list (
                'image_size' => $imageSize,
                'file_size'  => $fileSize,
            ) = $this->imageInfoFromUrl($file->getUrl());
        }
        return [
            'width'     => isset($imageSize[0]) ? $imageSize[0] : null,
            'height'    => isset($imageSize[1]) ? $imageSize[1] : null,
            'type'      => isset($imageSize[2]) ? $imageSize[2] : null,
            'file_size' => $fileSize,
        ];
    }
    
    /**
     * Returns array(
     *    'file_size' => <file size in bytes>
     *    'image_size' => <result of getimagesize()>
     * )
     * 
     * @param string $url
     * @return array
     */
    private function imageInfoFromUrl(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'header' => 'Range: bytes=0-65536',
            ]
        ]);
        $imageData = @file_get_contents($url, false, $context, 0, 65536);
        // $http_response_header is setted by PHP in file_get_contents()
        $httpHeaders = $this->parseHttpHeaders($http_response_header);
        $fileSize = $this->fileSizeFromHttpHeaders($httpHeaders);
        if (empty($fileSize)) {
            throw new Exception\HttpException("Cannot get size of file \"{$url}\"");
        }
        $imageSize = getimagesizefromstring($imageData);
        if (!isset($imageSize['0']) || !isset($imageSize['1']) || !isset($imageSize['2'])) {
            throw new Exception\ImageInfoException("Cannot get size of image \"{$url}\"");
        }
        return [
            'file_size' => $fileSize,
            'image_size' => $imageSize,
        ];
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
    
    private function imageSizeFromFile(string $path)
    {
        $imagedata = file_get_contents($path, false, null, 0, 65536);
        return getimagesizefromstring($imagedata);
    }
}