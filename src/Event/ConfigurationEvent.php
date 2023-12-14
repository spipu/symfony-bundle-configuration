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

namespace Spipu\ConfigurationBundle\Event;

use Spipu\ConfigurationBundle\Entity\Definition;
use Symfony\Contracts\EventDispatcher\Event;

class ConfigurationEvent extends Event
{
    public const PREFIX_NAME = 'spipu.configuration.';

    private Definition $configDefinition;
    private ?string $scope;

    public function __construct(Definition $configDefinition, ?string $scope)
    {
        $this->configDefinition = $configDefinition;
        $this->scope = $scope;
    }

    public function getGlobalEventCode(): string
    {
        return static::PREFIX_NAME . 'all';
    }

    public function getSpecificEventCode(): string
    {
        return static::PREFIX_NAME . $this->configDefinition->getCode();
    }

    public function getConfigDefinition(): Definition
    {
        return $this->configDefinition;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }
}
