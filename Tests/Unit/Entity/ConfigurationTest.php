<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Entity\Configuration;

class ConfigurationTest extends TestCase
{
    public function testEntity()
    {
        $entity = new Configuration();

        $entity->setCode('code');
        $this->assertSame('code', $entity->getCode());

        $entity->setValue('value');
        $this->assertSame('value', $entity->getValue());

        $setId = \Closure::bind(
            function ($value) {
                $this->id = $value;
            },
            $entity,
            $entity
        );

        $setId(1);
        $this->assertSame(1, $entity->getId());
    }
}
