<?php

declare(strict_types=1);

namespace FactorioItemBrowserTestSerializer\PortalApi\Server;

use FactorioItemBrowser\PortalApi\Server\Serializer\SerializerFactory;
use Interop\Container\ContainerInterface;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The test case extension for the serializer tests.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SerializerTestCase extends TestCase
{
    /**
     * Creates and returns the serializer.
     * @return SerializerInterface
     */
    protected function createSerializer(): SerializerInterface
    {
        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $serializerFactory = new SerializerFactory();
        return $serializerFactory($container, SerializerInterface::class);
    }

    /**
     * Asserts that the serialized version of the object equals the expected data structure.
     * @param array<mixed> $expectedData
     * @param object $object
     */
    protected function assertSerializedObject(array $expectedData, object $object): void
    {
        $serializer = $this->createSerializer();
        $serializedObject = $serializer->serialize($object, 'json');

        $this->assertEquals($expectedData, json_decode($serializedObject, true));
    }

    /**
     * Asserts that the deserialized version of the data equals the expected object.
     * @param object $expectedObject
     * @param array<mixed> $data
     */
    protected function assertDeserializedData(object $expectedObject, array $data): void
    {
        $serializer = $this->createSerializer();
        $deserializedData = $serializer->deserialize((string) json_encode($data), get_class($expectedObject), 'json');

        $this->assertEquals($expectedObject, $deserializedData);
    }
}
