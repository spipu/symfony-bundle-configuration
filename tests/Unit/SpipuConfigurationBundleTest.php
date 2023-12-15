<?php
namespace Spipu\ConfigurationBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\SpipuConfigurationBundle;
use Spipu\CoreBundle\RolesHierarchyBundleInterface;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Form\Options\BooleanStatus;
use Spipu\UiBundle\Form\Options\YesNo;
use Symfony\Component\Config\Definition\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface;

class SpipuConfigurationBundleTest extends TestCase
{
    public function testBase()
    {
        $builder = SymfonyMock::getContainerBuilder($this);
        $configurator = SymfonyMock::getContainerConfigurator($this);

        $bundle = new SpipuConfigurationBundle();

        $this->assertInstanceOf(ConfigurableExtensionInterface::class, $bundle);

        $this->assertSame('spipu_configuration', $bundle->getContainerExtension()->getAlias());

        $this->assertInstanceOf(RolesHierarchyBundleInterface::class, $bundle);
        $this->assertInstanceOf(RoleDefinitionInterface::class, $bundle->getRolesHierarchy());

        $this->assertFalse($builder->hasParameter('spipu_configuration'));
        $bundle->loadExtension([], $configurator, $builder);
        $this->assertTrue($builder->hasParameter('spipu_configuration'));
        $this->assertSame([], $builder->getParameter('spipu_configuration'));
    }

    public function testConfigGood()
    {
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
                'scoped'    => false,
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
                'scoped'    => false,
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
                'scoped'    => false,
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
                'scoped'    => false,
                'type'      => 'string',
                'unit'      => null,
            ],
        ];

        $builder = SymfonyMock::getContainerBuilder($this);
        $configurator = SymfonyMock::getContainerConfigurator($this);
        $bundle = new SpipuConfigurationBundle();

        $this->assertFalse($builder->hasParameter('spipu_configuration'));
        $bundle->loadExtension($config, $configurator, $builder);
        $this->assertTrue($builder->hasParameter('spipu_configuration'));

        $this->assertSame($expected, $builder->getParameter('spipu_configuration'));
    }

    public function testConfigErrorSelectWithoutOptions()
    {
        $config = [
            'test.select'  => [
                'type'     => 'select',
                'required' => true
            ],
        ];

        $builder = SymfonyMock::getContainerBuilder($this);
        $configurator = SymfonyMock::getContainerConfigurator($this);
        $bundle = new SpipuConfigurationBundle();

        $this->assertFalse($builder->hasParameter('spipu_configuration'));

        $this->expectException(InvalidConfigurationException::class);
        $bundle->loadExtension($config, $configurator, $builder);
    }

    public function testConfigErrorOptionsWithoutSelect()
    {
        $config = [
            'test.string'  => [
                'type'     => 'string',
                'required' => true,
                'options'   => YesNo::class,
            ],
        ];

        $builder = SymfonyMock::getContainerBuilder($this);
        $configurator = SymfonyMock::getContainerConfigurator($this);
        $bundle = new SpipuConfigurationBundle();

        $this->assertFalse($builder->hasParameter('spipu_configuration'));

        $this->expectException(InvalidConfigurationException::class);
        $bundle->loadExtension($config, $configurator, $builder);
    }

    public function testConfigErrorFileTypeWithoutFile()
    {
        $config = [
            'test.string'   => [
                'type'      => 'string',
                'required'  => true,
                'file_type' => ['pdf'],
            ],
        ];

        $builder = SymfonyMock::getContainerBuilder($this);
        $configurator = SymfonyMock::getContainerConfigurator($this);
        $bundle = new SpipuConfigurationBundle();

        $this->assertFalse($builder->hasParameter('spipu_configuration'));

        $this->expectException(InvalidConfigurationException::class);
        $bundle->loadExtension($config, $configurator, $builder);
    }

    public function testConfigErrorDefaultValueWithEncrypted()
    {
        $config = [
            'test.string'   => [
                'type'     => 'encrypted',
                'required' => true,
                'default'  => 'foo',
            ],
        ];

        $builder = SymfonyMock::getContainerBuilder($this);
        $configurator = SymfonyMock::getContainerConfigurator($this);
        $bundle = new SpipuConfigurationBundle();

        $this->assertFalse($builder->hasParameter('spipu_configuration'));

        $this->expectException(InvalidConfigurationException::class);
        $bundle->loadExtension($config, $configurator, $builder);
    }
    public function testConfigurationMissingType()
    {
        $configs = [
            0 => [
                'mock.missing.type' => [
                ]
            ]
        ];

        $builder = SymfonyMock::getContainerBuilder($this);
        $bundle = new SpipuConfigurationBundle();
        $extension = $bundle->getContainerExtension();
        $configuration = new Configuration($bundle, $builder, $extension->getAlias());
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $processor->processConfiguration($configuration, $configs);
    }

    public function testConfigurationMissingRequired()
    {
        $configs = [
            0 => [
                'mock.missing.required' => [
                    'type' => 'string',
                ]
            ]
        ];

        $builder = SymfonyMock::getContainerBuilder($this);
        $bundle = new SpipuConfigurationBundle();
        $extension = $bundle->getContainerExtension();
        $configuration = new Configuration($bundle, $builder, $extension->getAlias());
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $processor->processConfiguration($configuration, $configs);
    }

    public function testConfigurationLight()
    {
        $expected = [
            'mock.config' => [
                'type'     => 'string',
                'required' => true,
            ]
        ];
        $configs = [0 => $expected];

        $expected['mock.config']['file_type'] = [];
        $expected['mock.config']['scoped'] = false;
        $expected['mock.config']['default'] = null;
        $expected['mock.config']['unit'] = null;
        $expected['mock.config']['help'] = null;

        $builder = SymfonyMock::getContainerBuilder($this);
        $bundle = new SpipuConfigurationBundle();
        $extension = $bundle->getContainerExtension();
        $configuration = new Configuration($bundle, $builder, $extension->getAlias());
        $processor = new Processor();

        $result = $processor->processConfiguration($configuration, $configs);

        $this->assertSame($expected, $result);
    }

    public function testConfigurationDefault()
    {
        $builder = SymfonyMock::getContainerBuilder($this);
        $bundle = new SpipuConfigurationBundle();
        $extension = $bundle->getContainerExtension();
        $configuration = new Configuration($bundle, $builder, $extension->getAlias());
        $processor = new Processor();

        $configs = [0 => ['mock.config' => ['type' => 'string', 'required' => false, 'default'  => 'test']]];

        $result = $processor->processConfiguration($configuration, $configs);
        $this->assertSame('test', $result['mock.config']['default']);
    }

    public function testConfigurationUnit()
    {
        $builder = SymfonyMock::getContainerBuilder($this);
        $bundle = new SpipuConfigurationBundle();
        $extension = $bundle->getContainerExtension();
        $configuration = new Configuration($bundle, $builder, $extension->getAlias());
        $processor = new Processor();

        $configs = [0 => ['mock.config' => ['type' => 'string', 'required' => false, 'unit'  => 'test']]];

        $result = $processor->processConfiguration($configuration, $configs);
        $this->assertSame('test', $result['mock.config']['unit']);
    }

    public function testConfigurationHelp()
    {
        $builder = SymfonyMock::getContainerBuilder($this);
        $bundle = new SpipuConfigurationBundle();
        $extension = $bundle->getContainerExtension();
        $configuration = new Configuration($bundle, $builder, $extension->getAlias());
        $processor = new Processor();

        $configs = [0 => ['mock.config' => ['type' => 'string', 'required' => false, 'help'  => 'test']]];

        $result = $processor->processConfiguration($configuration, $configs);
        $this->assertSame('test', $result['mock.config']['help']);
    }

    public function testConfigurationFileType()
    {
        $builder = SymfonyMock::getContainerBuilder($this);
        $bundle = new SpipuConfigurationBundle();
        $extension = $bundle->getContainerExtension();
        $configuration = new Configuration($bundle, $builder, $extension->getAlias());
        $processor = new Processor();

        $configs = [0 => ['mock.config' => ['type' => 'string', 'required' => false, 'file_type'  => 'pdf']]];

        $result = $processor->processConfiguration($configuration, $configs);
        $this->assertSame(['pdf'], $result['mock.config']['file_type']);

        $configs = [0 => ['mock.config' => ['type' => 'string', 'required' => false, 'file_type'  => []]]];
        $this->expectException(InvalidConfigurationException::class);
        $processor->processConfiguration($configuration, $configs);
    }

    public function testConfigurationTypes()
    {
        $builder = SymfonyMock::getContainerBuilder($this);
        $bundle = new SpipuConfigurationBundle();
        $extension = $bundle->getContainerExtension();
        $configuration = new Configuration($bundle, $builder, $extension->getAlias());
        $processor = new Processor();

        $types = $bundle->getAvailableTypes();
        $expected = [
            'boolean',
            'color',
            'email',
            'encrypted',
            'file',
            'float',
            'integer',
            'password',
            'select',
            'string',
            'text',
            'url',
        ];
        $this->assertSame($expected, $types);

        foreach ($types as $type) {
            $configs = [0 => ['mock.config' => ['type' => $type, 'required' => false]]];
            $result = $processor->processConfiguration($configuration, $configs);
            $this->assertSame($type, $result['mock.config']['type']);
        }

        $configs = [0 => ['mock.config' => ['type' => 'wrong', 'required' => false]]];
        $this->expectException(InvalidConfigurationException::class);
        $processor->processConfiguration($configuration, $configs);
    }

    public function testConfigurationOptions()
    {
        $builder = SymfonyMock::getContainerBuilder($this);
        $bundle = new SpipuConfigurationBundle();
        $extension = $bundle->getContainerExtension();
        $configuration = new Configuration($bundle, $builder, $extension->getAlias());
        $processor = new Processor();

        $configs = [0 => ['mock.config' => ['type' => 'select', 'required' => false, 'options'  => YesNo::class]]];

        $result = $processor->processConfiguration($configuration, $configs);
        $this->assertSame(YesNo::class, $result['mock.config']['options']);

        $configs = [0 => ['mock.config' => ['type' => 'select', 'required' => false, 'options'  => '']]];
        $this->expectException(InvalidConfigurationException::class);
        $processor->processConfiguration($configuration, $configs);
    }
}
