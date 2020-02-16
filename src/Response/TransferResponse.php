<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Response;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\InjectContentTypeTrait;
use Laminas\Diactoros\Stream;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TransferResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * The transfer data of the response.
     * @var mixed
     */
    protected $transfer;

    /**
     * Initializes the response.
     * @param mixed $transfer
     * @param int $status
     * @param array<string> $headers
     */
    public function __construct($transfer, $status = 200, array $headers = [])
    {
        parent::__construct('php://memory', $status, $this->injectContentType('application/json', $headers));

        $this->transfer = $transfer;
    }

    /**
     * Returns the transfer data of the response.
     * @return mixed
     */
    public function getTransfer()
    {
        return $this->transfer;
    }

    /**
     * Returns a client response with the serialized response as body.
     * @param string $serializedResponse
     * @return self
     */
    public function withSerializedResponse(string $serializedResponse): self
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($serializedResponse);
        $stream->rewind();
        return $this->withBody($stream);
    }
}
