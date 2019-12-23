<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Style;

use Exception;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Helper\IconsStyleBuilder;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\IconsStyleData;
use FactorioItemBrowser\PortalApi\Server\Transfer\NamesByTypes;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconsHandler implements RequestHandlerInterface
{
    /**
     * The API client.
     * @var ApiClientInterface
     */
    protected $apiClient;

    /**
     * The icons style builder.
     * @var IconsStyleBuilder
     */
    protected $iconsStyleBuilder;

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Initializes the handler.
     * @param ApiClientInterface $apiClient
     * @param IconsStyleBuilder $iconsStyleBuilder
     * @param SerializerInterface $portalApiServerSerializer
     */
    public function __construct(
        ApiClientInterface $apiClient,
        IconsStyleBuilder $iconsStyleBuilder,
        SerializerInterface $portalApiServerSerializer
    ) {
        $this->apiClient = $apiClient;
        $this->iconsStyleBuilder = $iconsStyleBuilder;
        $this->serializer = $portalApiServerSerializer;
    }

    /**
     * Handles the request.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PortalApiServerException
     * @todo Cleanup
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $namesByTypes = $this->parseRequestBody($request);

        $request = new GenericIconRequest();
        foreach ($namesByTypes->getValues() as $type => $names) {
            foreach ($names as $name) {
                $entity = new Entity();
                $entity->setType($type)
                       ->setName($name);

                $request->addEntity($entity);
            }
        }

        /** @var GenericIconResponse $response */
        $response = $this->apiClient->fetchResponse($request);
        foreach ($response->getIcons() as $icon) {
            $this->iconsStyleBuilder->processIcon($icon);
        }

        $x = new IconsStyleData();
        $x->setProcessedEntities($this->iconsStyleBuilder->getProcessedEntities())
            ->setStyle($this->iconsStyleBuilder->getStyle());

        return new TransferResponse($x);
    }

    /**
     * Parses the body of the request.
     * @param ServerRequestInterface $request
     * @return NamesByTypes
     * @throws PortalApiServerException
     */
    protected function parseRequestBody(ServerRequestInterface $request): NamesByTypes
    {
        try {
            $requestBody = $request->getBody()->getContents();
            return $this->serializer->deserialize($requestBody, NamesByTypes::class, 'json');
        } catch (Exception $e) {
            throw new PortalApiServerException('Invalid request'); // @todo exception handling
        }
    }
}
