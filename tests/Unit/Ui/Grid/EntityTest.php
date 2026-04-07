<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Ui\Grid;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Ui\Grid\Entity;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Entity::class)]
class EntityTest extends TestCase
{
    public function testEntity(): void
    {
        $entity = new Entity(
            'code.mock.test',
            'type',
            true,
            false,
            'default',
            'options',
            'unit',
            'help',
            ['test', 'ext']
        );

        $this->assertNull($entity->getId());
        $entity->setValue('test');
        $this->assertSame('test', $entity->getValue());
    }
}
