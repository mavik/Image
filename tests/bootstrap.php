<?php
define('MAGIC_LIBRARY', 'imagick');

require_once '../vendor/autoload.php';
require_once './HttpServer.php';

if (!extension_loaded(MAGIC_LIBRARY)) {
    $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
    dl($prefix . MAGIC_LIBRARY . '.' . PHP_SHLIB_SUFFIX);
}