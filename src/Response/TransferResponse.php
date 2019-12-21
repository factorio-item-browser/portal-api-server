<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Response;

use Zend\Diactoros\Response;
use Zend\Diactoros\Response\InjectContentTypeTrait;
use Zend\Diactoros\Stream;

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
     * The transfer object of the response.
     * @var object
     */
    protected $transfer;

    /**
     * Initializes the response.
     * @param object $transfer
     * @param int $status
     * @param array<string> $headers
     */
    public function __construct(object $transfer, $status = 200, array $headers = [])
    {
        parent::__construct('php://memory', $status, $this->injectContentType('application/json', $headers));

        $this->transfer = $transfer;
    }

    /**
     * Returns the transfer object of the response.
     * @return object
     */
    public function getTransfer(): object
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
