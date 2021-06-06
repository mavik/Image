<?php
declare(strict_types=1);

/*
 *  PHP Library for Image processing and creating thumbnails
 *  
 *  @package Mavik\Image
 *  @author Vitalii Marenkov <admin@mavik.com.ua>
 *  @copyright 2021 Vitalii Marenkov
 *  @license MIT; see LICENSE
 */

namespace Mavik\Image;

use Mavik\Image\Exception;
use Mavik\Image\Exception\FileException;

/**
 * Manipulations with pathes and URLs
 */
class FileName
{
    const DEFAULT_CONFIGURATION = [
        'base_url' => '',
        'web_root_dir' => '',
    ];

    /** @var array */
    private static $configuration = [];    
    
    /** @var string */
    private $src;
    
    /** @var int */
    private $srcType;

    /** @var string */
    private $path;
    
    /** @var string */
    private $url;

    public static function configure(array $configuration): void
    {
        self::$configuration = array_merge(
            self::DEFAULT_CONFIGURATION,
            self::$configuration,
            $configuration
        );
        
        self::$configuration['web_root_dir'] = stream_resolve_include_path(self::$configuration['web_root_dir']);
        if (empty(self::$configuration['web_root_dir'])) {
            throw new FileException("Configuration web_root_dir is not correct.");
        }        
        if (empty(self::$configuration['base_url'])) {
            throw new FileException("Configuration base_url can't be empty.");
        }

        if (substr(self::$configuration['base_url'], -1) !== '/') {
            self::$configuration['base_url'] .= '/';
        }                
        if (substr(self::$configuration['web_root_dir'], -1) !== '/') {
            self::$configuration['web_root_dir'] .= '/';
        }
    }
    
    public function __construct($src)
    {
        $this->src = $src;
    }
    
    public function getPath(): ?string
    {
        if (!isset($this->path)) {
            $this->init();
        }
        return $this->path;
    }
    
    public function getUrl(): string
    {
        if (!isset($this->url)) {
            $this->init();
        }
        return $this->url;
    }
    
    private function init(): void
    {        
        if (preg_match('/^(http|https)\:\/\//', $this->src)) {
            $this->initFromAbsoluteUrl();
        } elseif (file_exists($this->src)) {
            $this->initFromPath();
        } elseif (!preg_match('/^\w+\:\/\//', $this->src)) {
            $this->initFromRelativeUrl();
        } else {
            throw new FileException("\"{$this->src}\" is not recognized as path or URL.");
        }        
    }
    
    private function initFromAbsoluteUrl(): void
    {
        $this->url = $this->src;
        if ($this->isLocalUrl($this->src)) {
            $this->path = $this->absoluteUrlToPath($this->src);
        }
    }
    
    private function initFromRelativeUrl(): void
    {        
        $this->url = $this->relativeUrlToAbsolute($this->src);
        $this->path = $this->absoluteUrlToPath($this->url);
    }

    private function initFromPath(): void
    {        
        $this->path = stream_resolve_include_path($this->src);
        $this->url = $this->absolutePathToUrl($this->path);
    }

    private function isLocalUrl(string $url): bool
    {        
        $urlParts = parse_url($url);
        $baseUrlParts = parse_url(self::$configuration['base_url']);
        return
            $this->hostWithoutWww($urlParts['host']) === $this->hostWithoutWww($baseUrlParts['host']) &&
            strpos($urlParts['path'], $baseUrlParts['path']) === 0
        ;
    }
    
    private function hostWithoutWww(string $host): string
    {
        return strpos($host, 'www.') === 0
            ? substr($host, 4)
            : $host
        ;
    }
    
    private function absoluteUrlToPath(string $url): ?string
    {
        $baseUrlParts = parse_url(self::$configuration['base_url']);
        $urlParts = parse_url($url);
        if (isset($urlParts['query']) && $urlParts['query'] !== '') {
            return null;
        }
        $baseUrlPath = $baseUrlParts['path'] ?? '';        
        if ($baseUrlPath !== '' && strpos($urlParts['path'], $baseUrlPath) !== 0) {
            throw new FileException("URL \"{$url}\" can't be converted to path.");
        }        
        return self::$configuration['web_root_dir'] . substr($urlParts['path'], strlen($baseUrlPath));
    }
    
    private function relativeUrlToAbsolute(string $url): string
    {
        if (strpos($url, '/') === 0) {
            $baseUrlParts = parse_url(self::$configuration['base_url']);
            return $baseUrlParts['scheme'] . '://' . $baseUrlParts['host'] . $this->normalizePath($url);
        } else {
            return self::$configuration['base_url'] . $this->normalizePath($url);
        }
    }

    private function absolutePathToUrl(string $path): string
    {        
        if (strpos($path, self::$configuration['web_root_dir']) !== 0) {
            throw new FileException("Path \"{$path}\" is not in web directory");
        }
        return 
            self::$configuration['base_url'] . 
            substr(str_replace('\\', '/', $path), strlen(self::$configuration['web_root_dir']))
        ;
    }

    /**
     * Processes '.' and '..' in path
     * 
     * @throws Exception
     */
    private function normalizePath(string $path): string
    {        
        $parts = explode('/', $path);
        for ($i = count($parts) - 1; $i >= 0; --$i) {
            $part = $parts[$i];
            if ($part == '.') {
                unset($parts[$i]);
            } elseif ($part == '..') {
                if ($i == 0 || $i == 1 && $parts[0] === '') {
                    throw new Exception("Can't normalize path \"{$path}\"");
                }
                unset($parts[$i]);
                unset($parts[--$i]);
            }
        }
        return implode('/', $parts);
    }
}