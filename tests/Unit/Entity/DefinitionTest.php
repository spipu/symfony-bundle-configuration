<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Entity;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Definition::class)]
class DefinitionTest extends TestCase
{
    public function testEntityOk(): void
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

    public function testEntityKo(): void
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
