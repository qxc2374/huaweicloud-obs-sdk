<?php

declare(strict_types=1);

/**
 * Framework-neutral runtime smoke test without network I/O or OBS credentials.
 */

$autoload = $argv[1] ?? dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, sprintf("Composer autoloader not found: %s\n", $autoload));
    exit(1);
}

require $autoload;
require_once dirname(__DIR__) . '/obs-autoloader.php';

use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\MimeType;
use GuzzleHttp\Psr7\Utils as Psr7Utils;
use GuzzleHttp\Utils as GuzzleUtils;
use QianXiong\Internal\Common\Model;
use QianXiong\ObsClient;
use QianXiong\ObsException;
use Psr\Log\NullLogger;

function assertSmoke(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

foreach ([
    [Create::class, 'rejectionFor'],
    [Psr7Utils::class, 'streamFor'],
    [Psr7Utils::class, 'copyToStream'],
    [GuzzleUtils::class, 'headersFromLines'],
    [GuzzleUtils::class, 'isHostInNoProxy'],
    [GuzzleUtils::class, 'debugResource'],
    [MimeType::class, 'fromFilename'],
] as [$class, $method]) {
    assertSmoke(method_exists($class, $method), sprintf('Required API %s::%s() is unavailable.', $class, $method));
}

assertSmoke(class_exists(ObsClient::class), 'Backward-compatible SDK autoloader failed.');

$exampleConfig = require dirname(__DIR__) . '/examples/config.php';
assertSmoke(isset($exampleConfig['client']) && is_array($exampleConfig['client']), 'Example client config is invalid.');
assertSmoke(is_bool($exampleConfig['client']['ssl_verify']), 'Example ssl_verify config must be boolean.');
assertSmoke(is_int($exampleConfig['client']['max_retry_count']), 'Example retry config must be integer.');

$model = new Model(['first' => 'value']);
$model->add('items', 'one')->add('items', 'two')->set('enabled', true);
assertSmoke($model->get('first') === 'value', 'Model get() failed.');
assertSmoke($model->get('items') === ['one', 'two'], 'Model add() failed.');
assertSmoke($model->toArray()['enabled'] === true, 'Model toArray() failed.');

$client = new ObsClient([
    'key' => 'test-access-key',
    'secret' => 'test-secret-key',
    'endpoint' => 'https://obs.example.invalid',
    'logger' => new NullLogger(),
]);

$signedUrl = $client->createSignedUrl([
    'Method' => 'GET',
    'Bucket' => 'test-bucket',
    'Key' => 'space key.txt',
]);
assertSmoke(str_contains($signedUrl['SignedUrl'], 'Signature='), 'V2 signed URL was not generated.');
assertSmoke(isset($signedUrl['ActualSignedRequestHeaders']['Host']), 'Signed request headers are missing.');

$v4Client = new ObsClient([
    'key' => 'test-access-key',
    'secret' => 'test-secret-key',
    'endpoint' => 'https://obs.example.invalid',
    'region' => 'cn-north-4',
    'signature' => ObsClient::SignatureV4,
    'logger' => new NullLogger(),
]);
$v4Url = $v4Client->createSignedUrl(['Method' => 'GET', 'Bucket' => 'test-bucket']);
assertSmoke(str_contains($v4Url['SignedUrl'], 'X-Amz-Signature='), 'V4 signed URL was not generated.');

$post = $v4Client->createPostSignature(['Bucket' => 'test-bucket', 'Key' => 'test-key']);
assertSmoke(isset($post['Policy'], $post['Signature']), 'V4 POST policy was not generated.');

$exceptionText = (string) new ObsException('smoke error');
assertSmoke(str_contains($exceptionText, 'smoke error'), 'ObsException string conversion failed.');

echo "OBS SDK smoke test passed.\n";