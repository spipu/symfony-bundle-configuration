<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;

class DefinitionTest extends TestCase
{
    public function testEntityOk()
    {
        $entity = new Definition(
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

        $this->assertSame('code.mock.test', $entity->getCode());
        $this->assertSame('type', $entity->getType());
        $this->assertSame(['code', 'mock', 'test'], $entity->getCategories());
        $this->assertSame('code', $entity->getMainCategory());
        $this->assertSame('mock.test', $entity->getSubCategories());
        $this->assertTrue($entity->isRequired());
        $this->assertFalse($entity->isScoped());
        $this->assertSame('default', $entity->getDefault());
        $this->assertSame('options', $entity->getOptions());
        $this->assertSame('unit', $entity->getUnit());
        $this->assertSame('help', $entity->getHelp());
        $this->assertSame(['test', 'ext'], $entity->getFileTypes());
    }

    public function testEntityKo()
    {
        $this->expectException(ConfigurationException::class);
        new Definition(
            'wrong',
            'type',
            true,
            false,
            'default',
            'options',
            'unit',
            'help',
            ['test', 'ext']
        );
    }
}
