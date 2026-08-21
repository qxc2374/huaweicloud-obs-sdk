# Huawei Cloud OBS SDK for PHP 8.1+

本分支在保留 `QianXiong\ObsClient` 公共调用方式的前提下，将运行环境和依赖栈升级到现代 PHP 生态，适用于 PHP 8.1–8.5，以及 ThinkPHP、Laravel、Hyperf 等基于 Composer 的框架。

原依赖包： composer require obs/esdk-obs-php

## 环境要求

- PHP `8.1` 或更高版本（已验证 PHP 8.5）
- 扩展：`json`、`libxml`、`simplexml`
- 推荐扩展：`curl`（异步请求和更好的 HTTP 性能）、`openssl`（HTTPS）
- Composer 2

主要依赖边界：

- Guzzle `^7.5`
- Guzzle PSR-7 `^2.4.5`
- Monolog `^2.10 || ^3.0`
- PSR-7 `^1.1 || ^2.0`
- PSR-3 `^1.1 || ^2.0 || ^3.0`

这些约束可与当前主流版本的 ThinkPHP、Laravel 和 Hyperf 共存。SDK 使用 PSR-4 自动加载，不会注册第三方依赖的私有自动加载器。

## 安装

```bash
composer require qianxiong/huaweicloud-obs-sdk
```

开发或验证本源码时：

```bash
composer install
composer test
```

`composer test` 会运行全部源码语法检查和无网络冒烟测试，不需要真实 OBS 凭证。

## 基本使用

所有运行配置遵循 `.env -> config -> 容器/工厂 -> ObsClient` 的单向流转。业务代码和 SDK 源码都不直接读取 `.env`，也不应硬编码凭证或 endpoint。

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use QianXiong\ObsClient;

$config = require __DIR__ . '/config/obs.php'; // 由项目自己的 config 文件返回客户端配置
$client = new ObsClient($config);

$result = $client->putObject([
    'Bucket' => 'example-bucket',
    'Key' => 'example/hello.txt',
    'Body' => 'hello OBS',
]);
```

项目所需变量可参考仓库中的 `.env.example`。生产环境请从环境变量或密钥服务读取 AK/SK，不要把真实 `.env` 或凭证提交到代码仓库。HTTPS 默认应保持证书校验；如使用私有 CA，可将 `ssl_verify` 设置为 CA 文件路径。

仓库自带示例使用 `examples/config.php` 和 `examples/bootstrap.php`，运行前请将 `.env.example` 的变量注入当前进程，例如：

```bash
cp .env.example .env
set -a; . ./.env; set +a
php examples/ListObjectsSample.php
```

## 框架集成

SDK 客户端适合由框架容器创建并复用。构造参数 `logger` 接受任意 `Psr\Log\LoggerInterface`，可以直接接入框架日志。

### ThinkPHP

`config/obs.php` 仅在配置层读取 `.env`：

```php
<?php

return [
    'key' => env('obs.access_key', ''),
    'secret' => env('obs.secret_key', ''),
    'security_token' => env('obs.security_token'),
    'endpoint' => env('obs.endpoint', ''),
    'region' => env('obs.region', 'cn-north-4'),
    'signature' => env('obs.signature', 'obs'),
    'ssl_verify' => env('obs.ssl_verify', true),
    'max_retry_count' => env('obs.max_retry_count', 3),
    'connect_timeout' => env('obs.connect_timeout', 10),
    'socket_timeout' => env('obs.socket_timeout', 60),
];
```

在服务类的 `register()` 中只读取 config 并绑定客户端：

```php
use QianXiong\ObsClient;
use think\Service;

final class ObsService extends Service
{
    public function register(): void
    {
        $this->app->bind(ObsClient::class, function () {
            return new ObsClient(config('obs'));
        });
    }
}
```

业务类通过构造函数声明 `ObsClient` 即可由容器注入。

### Laravel

新增 `config/obs.php`，`env()` 只允许出现在配置文件中：

```php
<?php

return [
    'key' => env('OBS_ACCESS_KEY'),
    'secret' => env('OBS_SECRET_KEY'),
    'security_token' => env('OBS_SECURITY_TOKEN'),
    'endpoint' => env('OBS_ENDPOINT'),
    'region' => env('OBS_REGION', 'cn-north-4'),
    'signature' => env('OBS_SIGNATURE', 'obs'),
    'ssl_verify' => env('OBS_SSL_VERIFY', true),
    'max_retry_count' => env('OBS_MAX_RETRY_COUNT', 3),
    'connect_timeout' => env('OBS_CONNECT_TIMEOUT', 10),
    'socket_timeout' => env('OBS_SOCKET_TIMEOUT', 60),
];
```

在 Service Provider 中注册单例：

```php
use QianXiong\ObsClient;

$this->app->singleton(ObsClient::class, function ($app) {
    return new ObsClient(config('obs') + [
        'logger' => $app->make('log'),
    ]);
});
```

执行 `php artisan config:cache` 后，业务代码仍只读取缓存后的 config，不直接调用 `env()`。

### Hyperf

在 `config/autoload/obs.php` 中读取 `.env`：

```php
<?php

return [
    'key' => env('OBS_ACCESS_KEY', ''),
    'secret' => env('OBS_SECRET_KEY', ''),
    'security_token' => env('OBS_SECURITY_TOKEN'),
    'endpoint' => env('OBS_ENDPOINT', ''),
    'region' => env('OBS_REGION', 'cn-north-4'),
    'signature' => env('OBS_SIGNATURE', 'obs'),
    'ssl_verify' => env('OBS_SSL_VERIFY', true),
    'max_retry_count' => (int) env('OBS_MAX_RETRY_COUNT', 3),
    'connect_timeout' => (int) env('OBS_CONNECT_TIMEOUT', 10),
    'socket_timeout' => (int) env('OBS_SOCKET_TIMEOUT', 60),
];
```

使用工厂绑定，避免在业务代码中直接读取环境变量：

```php
// config/autoload/dependencies.php
return [
    QianXiong\ObsClient::class => App\Factory\ObsClientFactory::class,
];
```

```php
namespace App\Factory;

use Hyperf\Contract\ConfigInterface;
use QianXiong\ObsClient;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class ObsClientFactory
{
    public function __invoke(ContainerInterface $container): ObsClient
    {
        $config = $container->get(ConfigInterface::class)->get('obs');

        return new ObsClient($config + [
            'logger' => $container->get(LoggerInterface::class),
        ]);
    }
}
```

Hyperf/Swoole 常驻进程中可以复用凭证固定的客户端，但不要在并发请求之间调用 `refresh()` 切换凭证；多租户场景应按凭证创建独立实例。异步接口返回 Guzzle Promise，并不等同于 Swoole 协程客户端。

## 向后兼容说明

- 保留 `QianXiong\ObsClient`、动态 API 方法、参数数组及返回 `Model` 的方式。
- `obs-autoloader.php` 仅作为旧项目兼容入口；框架项目必须优先使用 Composer 的 `vendor/autoload.php`。
- 旧版随 SDK 私带的 Guzzle/Monolog 加载方式已移除，第三方库统一由项目 Composer 解析，避免与框架依赖重复加载。
- 依赖下限提升到 PHP 8.1；PHP 7.x 不受支持。