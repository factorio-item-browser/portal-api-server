<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Response;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The factory for the responses.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ResponseFactory implements ResponseFactoryInterface
{
    private const MAX_AGE = 3600;

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return (new Response())
            ->withStatus($code, $reasonPhrase)
            ->withHeader('Access-Control-Max-Age', (string) self::MAX_AGE);
    }
}
