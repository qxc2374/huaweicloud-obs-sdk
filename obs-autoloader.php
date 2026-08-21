<?php

declare(strict_types=1);

/**
 * Backward-compatible SDK autoloader.
 *
 * Applications should normally load Composer's vendor/autoload.php. This file
 * only registers the Obs namespace and intentionally leaves third-party
 * dependencies to Composer, avoiding conflicts with framework autoloaders.
 */
spl_autoload_register(
    static function (string $class): void {
        $prefix = 'QianXiong\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = __DIR__ . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require_once $file;
        }
    },
    true,
    true
);
