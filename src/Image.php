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
        ],
        'base_url' => '',
        'web_root_dir' => '',
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
        
        FileName::configure([
            'base_url' => $configuration['base_url'],
            'web_root_dir' => $configuration['web_root_dir'],
        ]);
    }

    /**
     * @param string $src Path or URL
     */
    public function __construct(string $src = null)
    {        
        if (!empty($src)) {
            $this->open($src);
        }
    }
    
    /**
     * @param string $src Path or URL
     */
    public function open(string $src)
    {
        $fileName = new FileName($src);
        $this->file = new ImageFile($fileName->getUrl(), $fileName->getPath());
    }

    /**
     * Static constructor
     * 
     * @param string $src Path or URL
     */
    public static function make(string $src): Image
    {
        return new self($src);
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
            $this->type = $this->file ? $this->file->getType() : null;
        }
        return $this->type;
    }
    
    public function getWidth(): int
    {
        if (!isset($this->width)) {
            $this->width = $this->file ? $this->file->getWidth() : null;
        }
        return $this->width;
    }    
    
    public function getHeight(): int
    {
        if (!isset($this->height)) {
            $this->height = $this->file ? $this->file->getHeight() : null;
        }
        return $this->height;
    }    
        
    public function getFileSize(): ?int
    {
        return $this->file ? $this->file->getFileSize() : null;
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
    
    private function getResource(): mix
    {
        if (!isset($this->resource)) {
            $this->resource = $this->getGraphicLibrary()->open(
                $this->file->getPath() ?? $this->file->getUrl(),
                $this->getType()
            );
        }
        return $this->resource;
    }
}
