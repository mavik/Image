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

class Image
{
    const DEFAULT_CONFIGURATION = [
        'graphic_library' => [
            'priority' => [
                'gmagick',
                'imagick',
                'gd2',
            ]
        ]
    ];

    /** @var array */
    private static $configuration = [];
    
    /** @var mix */
    private $resource;
    
    /** @var int constant IMAGETYPE_XXX */
    private $type;

    /** @var int */
    private $width;
    
    /** @var int */
    private $height;
 
    /** @var ImageFile */
    private $file;
    
    /** @var GraphicLibrary */
    private $graphicLibrary;
    
    public static function configure(array $configuration): void
    {
        self::$configuration = array_merge(
            self::DEFAULT_CONFIGURATION,
            self::$configuration,
            $configuration
        );            
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
        $this->file = new ImageFile($fileName);
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
    
    public function getUrl(): ?string
    {
        return $this->file ? $this->file->getUrl() : null;
    }
    
    public function getPath(): ?string
    {
        return $this->file ? $this->file->getPath() : null;
    }
    
    /**
     * @return int IMAGETYPE_XXX
     */
    public function getType(): int
    {
        if (!isset($this->type)) {
            $this->initImageInfoFromFile();
        }
        return $this->type;
    }
    
    public function getWidth(): int
    {
        if (!isset($this->width)) {
            $this->initImageInfoFromFile();
        }
        return $this->width;
    }    
    
    public function getHeight(): int
    {
        if (!isset($this->height)) {
            $this->initImageInfoFromFile();
        }
        return $this->height;
    }    
        
    public function getFileSize(): ?int
    {
        if (!isset($this->file)) {
            return null;
        }         
        if (empty($this->file->getSize())) {
            $this->initImageInfoFromFile();
        }
        return $this->file->getSize();
    }    
    
    public function save(string $path): Image
    {
        $this->getGraphicLibrary()->save($this->getResource(), $path, $this->getType());
    }
    
    private function getGraphicLibrary(): GraphicLibraryInterface
    {
        if (!isset($this->graphicLibrary)) {
            foreach (self::$configuration['graphic_library']['priority'] as $libraryName) {
                $className = 'Graphiclibrary\\' . ucfirst(strtolower($libraryName));
                if (class_exists($className) && $className::isInstalled()) {
                    $this->graphicLibrary = new $className(self::$configuration['graphic_library']);
                    break;
                }
                throw new Exception\ConfigurationException('Configuration error: None of the required graphics libraries are installed.');
            }
        }
        return $this->graphicLibrary;
    }
    
    private function getResource()
    {
        return $this->getGraphicLibrary()->open(
            $this->file->getPath() ?? $this->file->getUrl(),
            $this->getType()
        );
    }
    
    private function initImageInfoFromFile(): void
    {        
        $imageFileInfo = new ImageFileInfo();
        list(
            'width' => $this->width, 
            'height' => $this->height, 
            'type' => $this->type,
            'file_size' => $fileSize,
        ) = $imageFileInfo->imageInfo($this->file);
        $this->file->setSize($fileSize);
    }
}
