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

class Image
{
    /** @var array */
    private static $configuration;
    
    /** @var mix */
    private $resource;
    
    /** @var int constant IMG_XXX */
    private $type;

    /** @var int */
    private $width;
    
    /** @var int */
    private $height;
 
    /** @var File */
    public $file;
        
    public static function configure(array $configuration)
    {
        self::$configuration = $configuration;
    }

    /**
     * @param string $fileName Path or URL
     */
    public function __construct(string $fileName = null)
    {
        if (!empty($fileName)) {
            $this->open($fileName);
        }
    }
    
    /**
     * @param string $fileName Path or URL
     */
    public function open(string $fileName)
    {
        $this->file = new File($fileName);
    }

    /**
     * Static constructor
     * 
     * @param string $fileName Path or URL
     */
    public static function make(string $fileName): Image
    {
        return new self($fileName);
    }
    
    /**
     * Static constructor
     */
    public static function makeFromString(string $content): Image
    {
        $image = new self();
        $image->loadFromString($content);
    }
    
    public function getUrl(): string
    {
        return $this->file->getUrl();
    }
    
    public function getPath(): string
    {
        return $this->file->getPath();
    }
}
