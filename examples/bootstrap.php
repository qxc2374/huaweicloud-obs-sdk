<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

if ($config['client']['key'] === '' || $config['client']['secret'] === '' || $config['client']['endpoint'] === '') {
    throw new InvalidArgumentException(
        'Set OBS_ACCESS_KEY, OBS_SECRET_KEY and OBS_ENDPOINT in the environment before running an example.'
    );
}

$obsClient = QianXiong\ObsClient::factory($config['client']);
$bucketName = $config['bucket'];
$objectKey = $config['object_key'];
$sourceObjectKey = $config['source_object_key'];
$localFilePath = $config['local_file_path'];
$sampleFilePath = $config['sample_file_path'];
$signature = $config['client']['signature'];
$sslVerify = $config['client']['ssl_verify'];
$accessKey = $config['client']['key'];
$endpoint = $config['client']['endpoint'];

defined('OBS_EXAMPLE_LOCAL_FILE_PATH') || define('OBS_EXAMPLE_LOCAL_FILE_PATH', $localFilePath);
defined('OBS_EXAMPLE_SAMPLE_FILE_PATH') || define('OBS_EXAMPLE_SAMPLE_FILE_PATH', $sampleFilePath);
