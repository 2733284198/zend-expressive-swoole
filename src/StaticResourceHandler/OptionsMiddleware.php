<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-swoole for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-swoole/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Swoole\StaticResourceHandler;

use Swoole\Http\Request;

class OptionsMiddleware implements MiddlewareInterface
{
    public function __invoke(Request $request, string $filename, callable $next) : StaticResourceResponse
    {
        $response = $next($request, $filename);

        $server = $request->server;
        if ($server['request_method'] !== 'OPTIONS') {
            return $response;
        }

        $response->addHeader('Allow', 'GET, HEAD, OPTIONS');
        $response->disableContent();
        return $response;
    }
}
