<?php

declare(strict_types=1);

$env = static function (string $name, mixed $default = null): mixed {
    if (array_key_exists($name, $_ENV)) {
        return $_ENV[$name];
    }

    if (array_key_exists($name, $_SERVER)) {
        return $_SERVER[$name];
    }

    $value = getenv($name);

    return $value === false ? $default : $value;
};

$boolean = static function (string $name, bool $default) use ($env): bool {
    $value = $env($name);
    if ($value === null || $value === '') {
        return $default;
    }

    $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    return $parsed ?? $default;
};

return [
    'client' => [
        'key' => (string) $env('OBS_ACCESS_KEY', ''),
        'secret' => (string) $env('OBS_SECRET_KEY', ''),
        'security_token' => $env('OBS_SECURITY_TOKEN') ?: null,
        'endpoint' => (string) $env('OBS_ENDPOINT', ''),
        'region' => (string) $env('OBS_REGION', 'cn-north-4'),
        'signature' => (string) $env('OBS_SIGNATURE', 'obs'),
        'ssl_verify' => $boolean('OBS_SSL_VERIFY', true),
        'path_style' => $boolean('OBS_PATH_STYLE', false),
        'max_retry_count' => (int) $env('OBS_MAX_RETRY_COUNT', 3),
        'connect_timeout' => (int) $env('OBS_CONNECT_TIMEOUT', 10),
        'socket_timeout' => (int) $env('OBS_SOCKET_TIMEOUT', 60),
    ],
    'bucket' => (string) $env('OBS_BUCKET', 'my-obs-bucket-demo'),
    'object_key' => (string) $env('OBS_OBJECT_KEY', 'my-obs-object-key-demo'),
    'source_object_key' => (string) $env('OBS_SOURCE_OBJECT_KEY', 'my-obs-source-object-key-demo'),
    'local_file_path' => (string) $env('OBS_LOCAL_FILE_PATH', '/tmp/obs-download.bin'),
    'sample_file_path' => (string) $env('OBS_SAMPLE_FILE_PATH', '/tmp/obs-sample.bin'),
];
