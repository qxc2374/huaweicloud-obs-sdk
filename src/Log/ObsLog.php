<?php

declare(strict_types=1);

/**
 * Copyright 2019 Huawei Technologies Co.,Ltd.
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use
 * this file except in compliance with the License.  You may obtain a copy of the
 * License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed
 * under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 * CONDITIONS OF ANY KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations under the License.
 *
 */

namespace QianXiong\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

final class ObsLog
{
    private static ?LoggerInterface $logger = null;

    public static function initLog(array $logConfig = []): void
    {
        $config = array_replace(ObsConfig::LOG_FILE_CONFIG, $logConfig);
        $path = rtrim((string) $config['FilePath'], '/\\');
        if ($path === '') {
            throw new \InvalidArgumentException('Log FilePath cannot be empty');
        }
        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Unable to create log directory: %s', $path));
        }

        $handler = new RotatingFileHandler(
            $path . DIRECTORY_SEPARATOR . (string) $config['FileName'],
            max(0, (int) $config['MaxFiles']),
            $config['Level']
        );
        $handler->setFormatter(new LineFormatter("[%datetime%][%level_name%] %message%\n"));
        $logger = new Logger('obs_sdk');
        $logger->pushHandler($handler);
        self::$logger = $logger;
    }

    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function reset(): void
    {
        self::$logger = null;
    }

    public static function commonLog($level, string $format, mixed $arg1 = null, mixed $arg2 = null): void
    {
        $message = ($arg1 === null && $arg2 === null) ? urldecode($format) : sprintf($format, $arg1, $arg2);
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];
        $location = isset($caller['file'], $caller['line']) ? '[' . basename($caller['file']) . ':' . $caller['line'] . '] ' : '';
        (self::$logger ??= new NullLogger())->log(self::normalizeLevel($level), $location . $message);
    }

    private static function normalizeLevel($level): string
    {
        if (is_string($level)) {
            return strtolower($level);
        }
        return match ($level) {
            Logger::DEBUG => LogLevel::DEBUG,
            Logger::INFO => LogLevel::INFO,
            Logger::NOTICE => LogLevel::NOTICE,
            Logger::WARNING => LogLevel::WARNING,
            Logger::ERROR => LogLevel::ERROR,
            Logger::CRITICAL => LogLevel::CRITICAL,
            Logger::ALERT => LogLevel::ALERT,
            Logger::EMERGENCY => LogLevel::EMERGENCY,
            default => LogLevel::INFO,
        };
    }
}
