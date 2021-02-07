<?php
/**
 * PHP Library for Image processing and creating thumbnails
 *
 * @package mavik\Image
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2021 Vitalii Marenkov
 * @license MIT; see LICENSE
 */
namespace mavik\Image;

class File
{
    /** @var string */
    private $path;
    
    /** @var string */
    private $url;
    
    /**
     * @param string $fileName Path or URL
     */
    public function __construct(string $fileName)
    {
        if ($this->isUrl($fileName)) {
            $this->url = $fileName;
        } else {
            $this->path = $fileName;
        }
    }
    
    private function isUrl(string $file): bool
    {
        return preg_match('/^\w+\:\/\//', $file);
    }
    
    public function getPath(): string
    {
        return $this->path;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
