<?php

/**
 * The config file used by Doctrine's CLI tools.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server;

use Doctrine\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\HelperSet;

/* @var ContainerInterface $container */
$container = require(__DIR__  . '/container.php');
/* @var EntityManagerInterface $entityManager */
$entityManager = $container->get(EntityManagerInterface::class);

return new HelperSet([
    'em' => new EntityManagerHelper($entityManager),
    'configuration' => new ConfigurationHelper(
        $entityManager->getConnection(),
        $container->get('doctrine.migrations.orm_default')
    ),
]);
