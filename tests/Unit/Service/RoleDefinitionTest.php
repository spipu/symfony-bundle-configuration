<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Tests\Unit\Service\RoleDefinitionUiTest;
use Spipu\ConfigurationBundle\Service\RoleDefinition;

class RoleDefinitionTest extends TestCase
{

    public function testService()
    {
        $items = RoleDefinitionUiTest::loadRoles($this, new RoleDefinition());

        $this->assertEquals(3, count($items));

        $this->assertArrayHasKey('ROLE_ADMIN_MANAGE_CONFIGURATION_SHOW', $items);
        $this->assertArrayHasKey('ROLE_ADMIN_MANAGE_CONFIGURATION_EDIT', $items);
        $this->assertArrayHasKey('ROLE_ADMIN_MANAGE_CONFIGURATION', $items);

        $this->assertEquals(Item::TYPE_ROLE, $items['ROLE_ADMIN_MANAGE_CONFIGURATION_SHOW']->getType());
        $this->assertEquals(Item::TYPE_ROLE, $items['ROLE_ADMIN_MANAGE_CONFIGURATION_EDIT']->getType());
        $this->assertEquals(Item::TYPE_ROLE, $items['ROLE_ADMIN_MANAGE_CONFIGURATION']->getType());

        Item::resetAll();
    }
}
