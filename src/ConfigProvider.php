<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-swoole for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-swoole/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Swoole;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Server as SwooleHttpServer;
use Zend\Expressive\Swoole\HotCodeReload\FileWatcher\InotifyFileWatcher;
use Zend\Expressive\Swoole\HotCodeReload\FileWatcherInterface;
use Zend\Expressive\Swoole\HotCodeReload\Reloader;
use Zend\Expressive\Swoole\HotCodeReload\ReloaderFactory;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use function extension_loaded;

class ConfigProvider
{
    public function __invoke() : array
    {
        $config = PHP_SAPI === 'cli' && extension_loaded('swoole')
            ? ['dependencies' => $this->getDependencies()]
            : [];

        $config['zend-expressive-swoole'] = $this->getDefaultConfig();

        return $config;
    }

    public function getDefaultConfig() : array
    {
        return [
            'swoole-http-server' => [
                // A prefix for the process name of the master process and workers.
                // By default the master process will be named `expressive-master`,
                // each http worker `expressive-worker-n` and each task worker
                // `expressive-task-worker-n` where n is the id of the worker
                'process-name' => 'expressive',
                'options' => [
                    // We set a default for this. Without one, Swoole\Http\Server
                    // defaults to the value of `ulimit -n`. Unfortunately, in
                    // virtualized or containerized environments, this often
                    // reports higher than the host container allows. 1024 is a
                    // sane default; users should check their host system, however,
                    // and set a production value to match.
                    'max_conn' => 1024,
                ],
                'static-files' => [
                    'enable' => true,
                ],
            ],
        ];
    }

    public function getDependencies() : array
    {
        return [
            'factories'  => [
                Command\ReloadCommand::class           => Command\ReloadCommandFactory::class,
                Command\StartCommand::class            => Command\StartCommandFactory::class,
                Command\StatusCommand::class           => Command\StatusCommandFactory::class,
                Command\StopCommand::class             => Command\StopCommandFactory::class,
                Log\AccessLogInterface::class          => Log\AccessLogFactory::class,
                Log\SwooleLoggerFactory::SWOOLE_LOGGER => Log\SwooleLoggerFactory::class,
                PidManager::class                      => PidManagerFactory::class,
                SwooleRequestHandlerRunner::class      => SwooleRequestHandlerRunnerFactory::class,
                ServerRequestInterface::class          => ServerRequestSwooleFactory::class,
                StaticResourceHandler::class           => StaticResourceHandlerFactory::class,
                SwooleHttpServer::class                => HttpServerFactory::class,
                Reloader::class                        => ReloaderFactory::class,
            ],
            'aliases' => [
                RequestHandlerRunner::class           => SwooleRequestHandlerRunner::class,
                StaticResourceHandlerInterface::class => StaticResourceHandler::class,
                FileWatcherInterface::class           => InotifyFileWatcher::class,
            ],
            'delegators' => [
                'Zend\Expressive\WhoopsPageHandler' => [
                    WhoopsPrettyPageHandlerDelegator::class,
                ],
            ],
        ];
    }
}
