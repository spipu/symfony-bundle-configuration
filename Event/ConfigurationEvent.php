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

    /**
     * @var Definition
     */
    private $configDefinition;

    /**
     * @var string|null
     */
    private $scope;

    /**
     * @param Definition $configDefinition
     * @param string|null $scope
     */
    public function __construct(Definition $configDefinition, ?string $scope)
    {

        $this->configDefinition = $configDefinition;
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getGlobalEventCode(): string
    {
        return static::PREFIX_NAME . 'all';
    }

    /**
     * @return string
     */
    public function getSpecificEventCode(): string
    {
        return static::PREFIX_NAME . $this->configDefinition->getCode();
    }

    /**
     * @return Definition
     */
    public function getConfigDefinition(): Definition
    {
        return $this->configDefinition;
    }

    /**
     * @return string|null
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }
}
