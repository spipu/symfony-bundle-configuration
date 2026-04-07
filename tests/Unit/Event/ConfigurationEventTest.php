<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Event;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Event\ConfigurationEvent;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(ConfigurationEvent::class)]
class ConfigurationEventTest extends TestCase
{
    public function testEvent(): void
    {
        $definition = new Definition('category.key', 'string', true, false, 'default', null, null, null, null);

        $event = new ConfigurationEvent($definition, 'my_scope');

        $this->assertSame($definition, $event->getConfigDefinition());
        $this->assertSame('my_scope', $event->getScope());
        $this->assertSame('spipu.configuration.all', $event->getGlobalEventCode());
        $this->assertSame('spipu.configuration.category.key', $event->getSpecificEventCode());
    }

    public function testEventWithNullScope(): void
    {
        $definition = new Definition('category.key', 'string', false, false, null, null, null, null, null);

        $event = new ConfigurationEvent($definition, null);

        $this->assertSame($definition, $event->getConfigDefinition());
        $this->assertNull($event->getScope());
    }
}
