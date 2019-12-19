<?php

declare(strict_types=1);

/**
 * The file providing the pipeline.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\PortalApi\Server;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware;
use Zend\Expressive\Router\Middleware\RouteMiddleware;
use Zend\Stratigility\Middleware\ErrorHandler;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
//    $app->pipe(Middleware\MetaMiddleware::class);
    $app->pipe(ErrorHandler::class);

    $app->pipe(ServerUrlMiddleware::class);
    $app->pipe(RouteMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);
    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);

//    $app->pipe(Middleware\AgentMiddleware::class);
//    $app->pipe(Middleware\RequestDeserializerMiddleware::class);
//    $app->pipe(Middleware\ResponseSerializerMiddleware::class);

    $app->pipe(DispatchMiddleware::class);
//    $app->pipe(Handler\NotFoundHandler::class);
};
