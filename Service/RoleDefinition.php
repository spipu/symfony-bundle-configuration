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

use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;

class RoleDefinition implements RoleDefinitionInterface
{
    /**
     * @return void
     */
    public function buildDefinition(): void
    {
        Item::load('ROLE_ADMIN_MANAGE_CONFIGURATION_SHOW')
            ->setLabel('spipu.configuration.role.admin_show')
            ->setWeight(10)
            ->addChild('ROLE_ADMIN');

        Item::load('ROLE_ADMIN_MANAGE_CONFIGURATION_EDIT')
            ->setLabel('spipu.configuration.role.admin_edit')
            ->setWeight(20)
            ->addChild('ROLE_ADMIN');

        Item::load('ROLE_ADMIN_MANAGE_CONFIGURATION')
            ->setLabel('spipu.configuration.role.admin')
            ->setWeight(30)
            ->addChild('ROLE_ADMIN_MANAGE_CONFIGURATION_SHOW')
            ->addChild('ROLE_ADMIN_MANAGE_CONFIGURATION_EDIT');

        Item::load('ROLE_SUPER_ADMIN')
            ->addChild('ROLE_ADMIN_MANAGE_CONFIGURATION');
    }
}
