<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Handler\Style;

use Exception;
use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Exception\ApiClientException;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedApiRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
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
 * The handler for providing the styles for icons.
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
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $namesByTypes = $this->parseRequestBody($request);
        $genericIconResponse = $this->fetchData($namesByTypes);
        $iconsStyleData = $this->createIconsStyleData($genericIconResponse);
        return new TransferResponse($iconsStyleData);
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
            throw new InvalidRequestException($e->getMessage(), $e);
        }
    }

    /**
     * Fetches the data from the API.
     * @param NamesByTypes $namesByTypes
     * @return GenericIconResponse
     * @throws PortalApiServerException
     */
    protected function fetchData(NamesByTypes $namesByTypes): GenericIconResponse
    {
        $request = new GenericIconRequest();

        foreach ($namesByTypes->getValues() as $type => $names) {
            foreach ($names as $name) {
                $entity = new Entity();
                $entity->setType($type)
                       ->setName($name);

                $request->addEntity($entity);
            }
        }

        try {
            /** @var GenericIconResponse $response */
            $response = $this->apiClient->fetchResponse($request);
        } catch (ApiClientException $e) {
            throw new FailedApiRequestException($e);
        }
        return $response;
    }

    /**
     * Creates the icons style data to send to the frontend.
     * @param GenericIconResponse $genericIconResponse
     * @return IconsStyleData
     */
    protected function createIconsStyleData(GenericIconResponse $genericIconResponse): IconsStyleData
    {
        foreach ($genericIconResponse->getIcons() as $icon) {
            $this->iconsStyleBuilder->processIcon($icon);
        }

        $iconsStyleData = new IconsStyleData();
        $iconsStyleData->setProcessedEntities($this->iconsStyleBuilder->getProcessedEntities())
                       ->setStyle($this->iconsStyleBuilder->getStyle());
        return $iconsStyleData;
    }
}
