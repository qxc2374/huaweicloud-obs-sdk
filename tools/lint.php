<?php

declare(strict_types=1);

/**
 * Lint every first-party source file without requiring a framework bootstrap.
 */

$root = dirname(__DIR__);
$directories = [$root . '/src', $root . '/tools', $root . '/examples'];
$files = [$root . '/obs-autoloader.php'];

foreach ($directories as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

sort($files);
$failed = false;

foreach ($files as $file) {
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1';
    $output = [];
    exec($command, $output, $exitCode);
    echo implode(PHP_EOL, $output) . PHP_EOL;
    if ($exitCode !== 0) {
        $failed = true;
    }
}

exit($failed ? 1 : 0);