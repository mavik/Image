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
    /** @var mix */
    protected $resource;
    
    /** @var int constant IMAGETYPE_XXX */
    private $type;
    
    /** @var ImageSize **/
    private $size;

    /** @var ImageFile */
    protected $file;
    
    /** @var Configuration */
    protected static $configuration;

    /** @var GraphicLibraryInterface */
    protected static $graphicLibrary;
    
    public static function configure(Configuration $configuration): void
    {
        static::$configuration = $configuration;
        $graphicLibraryClass = self::$configuration->graphicLibraryClass();
        static::$graphicLibrary = new $graphicLibraryClass;
    }

    private function __construct() {
        if (empty(static::$configuration)) {
            throw new LogicException('Method ' . static::class . ':configure must be called before creating instances.');
        }
    }
    
    /**
     * Create an instance from the file
     * 
     * @param string $src Path or URL
     */
    public static function create(string $src): self
    {
        $fileName = new FileName($src, static::$configuration->baseUri(), static::$configuration->webRootDirectory());
        $imageFile = new ImageFile($fileName);
        $image = new static();
        $image->file = $imageFile;
        return $image;
    }
    
    /**
     * Create an instance from the string
     */
    public static function createFromString(string $content): self
    {
        $image = new static();
        $image->resource = static::$graphicLibrary->loadFromString($content);
        $info = getimagesizefromstring($content);
        $image->type = $info[2];
        return $image;
    }

    public function save(string $path): Image
    {
        static::$graphicLibrary->save($this->getResource(), $path, $this->getType());
        return $this;
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
     * @return int|null IMAGETYPE_XXX
     */
    public function getType(): int
    {
        if (!isset($this->type)) {
            $this->type = $this->file->getType();
        }
        return $this->type;
    }

    public function getSize(): ImageSize
    {
        if (!isset($this->size)) {
            // Resource is not creating in constructor
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
            static::$graphicLibrary->getWidth($this->resource),
            static::$graphicLibrary->getHeight($this->resource)
        );
    }
    
    public function getFileSize(): ?int
    {
        return $this->file ? $this->file->getFileSize() : null;
    }    
    
    public function resize(int $width, int $height): Image
    {
        $this->resource = static::$graphicLibrary->resize($this->getResource(), $width, $height);
        $this->resetSize();
        return $this;
    }

    public function crop(int $x, int $y, int $width, int $height): Image
    {
        $this->resource = static::$graphicLibrary->crop($this->getResource(), $x, $y, $width, $height);
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
        $this->resource = static::$graphicLibrary->cropAndResize($this->getResource(), $x, $y, $width, $height, $toWidth, $toHeight);
        $this->resetSize();
        return $this;
    }
    
    /**
     * @return mix Depends on graphic library
     */
    protected function getResource()
    {
        if (!isset($this->resource)) {
            $this->resource = static::$graphicLibrary->load($this->file);
        }
        return $this->resource;
    }
    
    /**
     * Unset width and height
     */
    protected function resetSize(): void
    {
        $this->size = null;
    }
    
    public function __clone()
    {
        if (isset($this->file)) {
            $this->file = clone $this->file;
        }
        if (isset(static::$graphicLibrary)) {
            static::$graphicLibrary = clone static::$graphicLibrary;
        }
        if (isset($this->resource)) {
            $this->resource = static::$graphicLibrary->clone($this->resource);
        }
    }
}