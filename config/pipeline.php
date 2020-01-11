<?php

declare(strict_types=1);

/**
 * The file providing the pipeline.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\PortalApi\Server;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Psr\Container\ContainerInterface;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->pipe(Middleware\MetaMiddleware::class);
    $app->pipe(ErrorHandler::class);

    $app->pipe(ServerUrlMiddleware::class);
    $app->pipe(RouteMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);
    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);

    $app->pipe(Middleware\ApiClientMiddleware::class);
    $app->pipe(Middleware\CorsHeaderMiddleware::class);
    $app->pipe(Middleware\ResponseSerializerMiddleware::class);

    $app->pipe(DispatchMiddleware::class);
    $app->pipe(Handler\NotFoundHandler::class);
};
