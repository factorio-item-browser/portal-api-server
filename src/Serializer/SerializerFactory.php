<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Serializer;

use FactorioItemBrowser\PortalApi\Server\Constant\ConfigKey;
use Interop\Container\ContainerInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * The factory for the serializer of the Portal PI server.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SerializerFactory implements FactoryInterface
{
    /**
     * Creates the serializer.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return SerializerInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SerializerInterface
    {
        $builder = SerializerBuilder::create();
        $builder
            ->addMetadataDir(
                (string) realpath(__DIR__ . '/../../config/serializer'),
                'FactorioItemBrowser\PortalApi\Server'
            )
            ->setPropertyNamingStrategy(new IdenticalPropertyNamingStrategy());

        $this->addCacheDirectory($container, $builder);

        return $builder->build();
    }

    /**
     * Adds the cache directory from the config to the builder.
     * @param ContainerInterface $container
     * @param SerializerBuilder $builder
     */
    protected function addCacheDirectory(ContainerInterface $container, SerializerBuilder $builder): void
    {
        $config = $container->get('config');
        $libraryConfig = $config[ConfigKey::PROJECT][ConfigKey::PORTAL_API_SERVER] ?? [];
        $cacheDir = (string) ($libraryConfig[ConfigKey::CACHE_DIR] ?? '');

        if ($cacheDir !== '') {
            $builder->setCacheDir($cacheDir);
        }
    }
}
