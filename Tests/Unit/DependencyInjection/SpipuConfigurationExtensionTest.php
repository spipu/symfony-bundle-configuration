<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\DependencyInjection\SpipuConfigurationConfiguration;
use Spipu\ConfigurationBundle\DependencyInjection\SpipuConfigurationExtension;
use Spipu\CoreBundle\DependencyInjection\RolesHierarchyExtensionExtensionInterface;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Form\Options\BooleanStatus;
use Spipu\UiBundle\Form\Options\YesNo;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class SpipuConfigurationExtensionTest extends TestCase
{
    public function testBase()
    {
        $builder = SymfonyMock::getContainerBuilder($this);

        $extension = new SpipuConfigurationExtension();

        $this->assertInstanceOf(ExtensionInterface::class, $extension);

        $this->assertSame('spipu_configuration', $extension->getAlias());

        $this->assertInstanceOf(RolesHierarchyExtensionExtensionInterface::class, $extension);
        $this->assertInstanceOf(RoleDefinitionInterface::class, $extension->getRolesHierarchy());

        $this->assertInstanceOf(ConfigurationExtensionInterface::class, $extension);
        $this->assertInstanceOf(SpipuConfigurationConfiguration::class, $extension->getConfiguration([], $builder));

        $this->assertFalse($builder->hasParameter('spipu_configuration'));
        $extension->load([], $builder);
        $this->assertTrue($builder->hasParameter('spipu_configuration'));
        $this->assertSame([], $builder->getParameter('spipu_configuration'));
    }

    public function testConfigGood()
    {
        $builder = SymfonyMock::getContainerBuilder($this);

        $extension = new SpipuConfigurationExtension();

        $config = [
            'test.string'  => [
                'type'     => 'string',
                'required' => false,
            ],
            'test.boolean'  => [
                'type'     => 'boolean',
                'required' => true,
            ],
            'test.select'  => [
                'type'     => 'select',
                'required' => true,
                'options'  => YesNo::class,
            ],
            'test.file'  => [
                'type'      => 'file',
                'required'  => true,
                'file_type' => 'pdf',
            ],
        ];

        $expected = [
            'test.boolean' => [
                'code'      => 'test.boolean',
                'default'   => null,
                'file_type' => [],
                'help'      => null,
                'options'   => BooleanStatus::class,
                'required'  => true,
                'type'      => 'boolean',
                'unit'      => null,
            ],
            'test.file' => [
                'code'      => 'test.file',
                'default'   => null,
                'file_type' => ['pdf'],
                'help'      => null,
                'options'   => null,
                'required'  => true,
                'type'      => 'file',
                'unit'      => null,
            ],
            'test.select' => [
                'code'      => 'test.select',
                'default'   => null,
                'file_type' => [],
                'help'      => null,
                'options'   => YesNo::class,
                'required'  => true,
                'type'      => 'select',
                'unit'      => null,
            ],
            'test.string' => [
                'code'      => 'test.string',
                'default'   => null,
                'file_type' => [],
                'help'      => null,
                'options'   => null,
                'required'  => false,
                'type'      => 'string',
                'unit'      => null,
            ],
        ];

        $this->assertFalse($builder->hasParameter('spipu_configuration'));
        $extension->load([0 => $config], $builder);
        $this->assertTrue($builder->hasParameter('spipu_configuration'));

        $this->assertSame($expected, $builder->getParameter('spipu_configuration'));
    }

    public function testConfigErrorSelectWithoutOptions()
    {
        $builder = SymfonyMock::getContainerBuilder($this);

        $extension = new SpipuConfigurationExtension();

        $config = [
            'test.select'  => [
                'type'     => 'select',
                'required' => true
            ],
        ];

        $this->assertFalse($builder->hasParameter('spipu_configuration'));

        $this->expectException(InvalidConfigurationException::class);
        $extension->load([0 => $config], $builder);
    }

    public function testConfigErrorOptionsWithoutSelect()
    {
        $builder = SymfonyMock::getContainerBuilder($this);

        $extension = new SpipuConfigurationExtension();

        $config = [
            'test.string'  => [
                'type'     => 'string',
                'required' => true,
                'options'   => YesNo::class,
            ],
        ];

        $this->assertFalse($builder->hasParameter('spipu_configuration'));

        $this->expectException(InvalidConfigurationException::class);
        $extension->load([0 => $config], $builder);
    }

    public function testConfigErrorFileTypeWithoutFile()
    {
        $builder = SymfonyMock::getContainerBuilder($this);

        $extension = new SpipuConfigurationExtension();

        $config = [
            'test.string'   => [
                'type'      => 'string',
                'required'  => true,
                'file_type' => ['pdf'],
            ],
        ];

        $this->assertFalse($builder->hasParameter('spipu_configuration'));

        $this->expectException(InvalidConfigurationException::class);
        $extension->load([0 => $config], $builder);
    }
}