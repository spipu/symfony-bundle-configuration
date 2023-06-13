<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Service;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Definitions
{
    private ContainerInterface $container;

    /**
     * @var Definition[]
     */
    private ?array $definitions = null;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    private function load(): void
    {
        if (is_array($this->definitions)) {
            return;
        }

        $configurations = $this->container->getParameter('spipu_configuration');

        $this->definitions = [];
        foreach ($configurations as $configuration) {
            $definition = new Definition(
                $configuration['code'],
                $configuration['type'],
                $configuration['required'],
                $configuration['scoped'],
                $configuration['default'],
                $configuration['options'],
                $configuration['unit'],
                $configuration['help'],
                $configuration['file_type']
            );

            $this->definitions[$definition->getCode()] = $definition;
        }
    }

    /**
     * @return Definition[]
     */
    public function getAll(): array
    {
        $this->load();

        return $this->definitions;
    }

    public function get(string $key): Definition
    {
        $this->load();

        if (!array_key_exists($key, $this->definitions)) {
            throw new ConfigurationException(sprintf('Unknown configuration key [%s]', $key));
        }

        return $this->definitions[$key];
    }
}
