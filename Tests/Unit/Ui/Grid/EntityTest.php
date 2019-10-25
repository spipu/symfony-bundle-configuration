<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Ui\Grid;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Ui\Grid\Entity;

class EntityTest extends TestCase
{
    public function testEntity()
    {
        $entity = new Entity(
            'code.mock.test',
            'type',
            true,
            'default',
            'options',
            'unit',
            ['test', 'ext']
        );

        $this->assertNull($entity->getId());
        $entity->setValue('test');
        $this->assertSame('test', $entity->getValue());
    }
}
