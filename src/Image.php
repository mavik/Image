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
    protected static $configuration = [];
    
    /** @var mix */
    protected $resource;
    
    /** @var int constant IMAGETYPE_XXX */
    private $type;
    
    /** @var ImageSize **/
    private $size;

    /** @var ImageFile */
    protected $file;
    
    /** @var GraphicLibraryInterface */
    protected $graphicLibrary;
    
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
     * Instances are created by factory methods
     */
    private function __construct() {}
    
    /**
     * Create an instance from file
     * 
     * @param string $src Path or URL
     */
    public static function create(string $src): Image
    {
        $image = new self();
        $image->load($src);
        return $image;
    }
    
    /**
     * Create an instance from string
     */
    public static function createFromString(string $content): Image
    {
        $image = new self();
        $image->loadFromString($content);
        return $image;
    }
        
    /**
     * @param string $src Path or URL
     */
    private function load(string $src)
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

    public function getSize(): ImageSize
    {
        if (!isset($this->size)) {
            if (isset($this->resource)) {
                $this->size = $this->getImageSizeFromResource();
            } elseif (isset($this->file)) {
                $this->size = $this->file->getImageSize();
            } else {
                throw new LogicException();
            }
        }
        return $this->size;
    }
    
    /**
     * Alias for getSize()->width
     */
    public function getWidth(): int
    {
        return $this->getSize()->width;
    }
    
    /**
     * Alias for getSize()->height
     */
    public function getHeight(): int
    {
        return $this->getSize()->height;
    }

    private function getImageSizeFromResource(): ImageSize
    {
        return new ImageSize(
            $this->graphicLibrary->getWidth($this->resource),
            $this->graphicLibrary->getHeight($this->resource)
        );
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
        $this->size = null;
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