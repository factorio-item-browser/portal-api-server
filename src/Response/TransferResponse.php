<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Response;

use JMS\Serializer\SerializerInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\InjectContentTypeTrait;
use Laminas\Diactoros\Stream;

/**
 * The response wrapping the transfer object.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TransferResponse extends Response
{
    use InjectContentTypeTrait;

    /** @var mixed */
    private $transfer;

    /**
     * @param mixed $transfer
     * @param int $statusCode
     * @param array<string, string> $headers
     */
    public function __construct($transfer, int $statusCode = 200, array $headers = [])
    {
        parent::__construct('php://memory', $statusCode, $this->injectContentType('application/json', $headers));
        $this->transfer = $transfer;
    }

    /**
     * @return mixed
     */
    public function getTransfer()
    {
        return $this->transfer;
    }

    public function withSerializer(SerializerInterface $serializer): self
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($serializer->serialize($this->transfer, 'json'));
        $stream->rewind();
        return $this->withBody($stream);
    }
}
