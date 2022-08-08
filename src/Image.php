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
    protected $resource;
    
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
        
    /**
     * @param string $src Path or URL
     */
    public function open(string $src)
    {
        $fileName = new FileName($src);
        $this->file = new ImageFile($fileName->getUrl(), $fileName->getPath());
    }

    public function save(string $path): Image
    {
        $this->getGraphicLibrary()->save($this->getResource(), $path, $this->getType());
        return $this;
    }    
    
    public function close(): void
    {
        $this->getGraphicLibrary()->close($this->getResource());
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
            if (isset($this->resource)) {
                $this->width = $this->graphicLibrary->getWidth($this->resource);
            } elseif (isset($this->file)) {
                $this->width = $this->file->getWidth();
            }
        }
        return $this->width;
    }    
    
    public function getHeight(): int
    {
        if (!isset($this->height)) {
            if (isset($this->resource)) {
                $this->height = $this->graphicLibrary->getHeight($this->resource);
            } elseif (isset($this->file)) {
                $this->height = $this->file->getHeight();
            }
        }
        return $this->height;
    }    
        
    public function getFileSize(): ?int
    {
        return $this->file ? $this->file->getFileSize() : null;
    }    
    
    public function resize(int $width, int $height): Image
    {
        $this->resource = $this->getGraphicLibrary()->resize($this->getResource(), $width, $height);
        $this->resetSize();
        return $this;
    }

    public function crop(int $x, int $y, int $width, int $height): Image
    {
        $this->resource = $this->getGraphicLibrary()->crop($this->getResource(), $x, $y, $width, $height);
        $this->resetSize();
        return $this;
    }

    public function cropAndResize(
        int $x,
        int $y,
        int $width,
        int $height,
        int $toWidth,
        int $toHeight
    ) {
        $this->resource = $this->getGraphicLibrary()->cropAndResize($this->getResource(), $x, $y, $width, $height, $toWidth, $toHeight);
        $this->resetSize();
        return $this;
    }

    protected function getGraphicLibrary(): GraphicLibraryInterface
    {
        if (!isset($this->graphicLibrary)) {
            foreach (self::$configuration['graphic_library']['priority'] as $libraryName) {
                $className = 'Mavik\\Image\\GraphicLibrary\\' . ucfirst(strtolower($libraryName));
                if (class_exists($className, true) && $className::isInstalled()) {
                    $this->graphicLibrary = new $className(self::$configuration['graphic_library']);
                    break;
                }
            }
            if (!isset($this->graphicLibrary)) {
                throw new Exception\ConfigurationException('Configuration error: None of the required graphics libraries are installed.');
            }
        }
        return $this->graphicLibrary;
    }
    
    /**
     * @return mix Depends on graphic library
     */
    protected function getResource()
    {
        if (!isset($this->resource)) {
            $this->resource = $this->getGraphicLibrary()->open(
                $this->file->getPath() ?? $this->file->getUrl(),
                $this->getType()
            );
        }
        return $this->resource;
    }
    
    /**
     * Unset width and height
     */
    protected function resetSize()
    {
        $this->width = null;
        $this->height= null;
    }
    
    public function __clone()
    {
        if (isset($this->file)) {
            $this->file = clone $this->file;
        }
        if (isset($this->graphicLibrary)) {
            $this->graphicLibrary = clone $this->graphicLibrary;
        }
        if (isset($this->resource)) {
            $this->resource = $this->graphicLibrary->clone($this->resource);
        }
    }
}