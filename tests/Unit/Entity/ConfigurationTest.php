<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Entity;

use Closure;
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

        $this->assertSame(null, $entity->getScope());
        $entity->setScope('');
        $this->assertSame(null, $entity->getScope());
        $entity->setScope('foo');
        $this->assertSame('foo', $entity->getScope());
        $entity->setScope(null);
        $this->assertSame(null, $entity->getScope());

        $setId = Closure::bind(
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
