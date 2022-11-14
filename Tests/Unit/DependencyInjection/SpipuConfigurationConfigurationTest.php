<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\DependencyInjection\SpipuConfigurationConfiguration;
use Spipu\UiBundle\Form\Options\YesNo;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class SpipuConfigurationConfigurationTest extends TestCase
{
    public function testMissingType()
    {
        $configs = [
            0 => [
                'mock.missing.type' => [
                ]
            ]
        ];

        $configuration = new SpipuConfigurationConfiguration();

        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $processor->processConfiguration($configuration, $configs);
    }

    public function testMissingRequired()
    {
        $configs = [
            0 => [
                'mock.missing.required' => [
                    'type' => 'string',
                ]
            ]
        ];

        $configuration = new SpipuConfigurationConfiguration();

        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $processor->processConfiguration($configuration, $configs);
    }

    public function testLight()
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

        $configuration = new SpipuConfigurationConfiguration();

        $processor = new Processor();

        $result = $processor->processConfiguration($configuration, $configs);

        $this->assertSame($expected, $result);
    }

    public function testDefault()
    {
        $configuration = new SpipuConfigurationConfiguration();
        $processor = new Processor();

        $configs = [0 => ['mock.config' => ['type' => 'string', 'required' => false, 'default'  => 'test']]];

        $result = $processor->processConfiguration($configuration, $configs);
        $this->assertSame('test', $result['mock.config']['default']);
    }

    public function testUnit()
    {
        $configuration = new SpipuConfigurationConfiguration();
        $processor = new Processor();

        $configs = [0 => ['mock.config' => ['type' => 'string', 'required' => false, 'unit'  => 'test']]];

        $result = $processor->processConfiguration($configuration, $configs);
        $this->assertSame('test', $result['mock.config']['unit']);
    }

    public function testHelp()
    {
        $configuration = new SpipuConfigurationConfiguration();
        $processor = new Processor();

        $configs = [0 => ['mock.config' => ['type' => 'string', 'required' => false, 'help'  => 'test']]];

        $result = $processor->processConfiguration($configuration, $configs);
        $this->assertSame('test', $result['mock.config']['help']);
    }

    public function testFileType()
    {
        $configuration = new SpipuConfigurationConfiguration();
        $processor = new Processor();

        $configs = [0 => ['mock.config' => ['type' => 'string', 'required' => false, 'file_type'  => 'pdf']]];

        $result = $processor->processConfiguration($configuration, $configs);
        $this->assertSame(['pdf'], $result['mock.config']['file_type']);

        $configs = [0 => ['mock.config' => ['type' => 'string', 'required' => false, 'file_type'  => []]]];
        $this->expectException(InvalidConfigurationException::class);
        $processor->processConfiguration($configuration, $configs);
    }

    public function testTypes()
    {
        $configuration = new SpipuConfigurationConfiguration();
        $processor = new Processor();

        $types = $configuration->getAvailableTypes();
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

    public function testOptions()
    {
        $configuration = new SpipuConfigurationConfiguration();
        $processor = new Processor();

        $configs = [0 => ['mock.config' => ['type' => 'select', 'required' => false, 'options'  => YesNo::class]]];

        $result = $processor->processConfiguration($configuration, $configs);
        $this->assertSame(YesNo::class, $result['mock.config']['options']);

        $configs = [0 => ['mock.config' => ['type' => 'select', 'required' => false, 'options'  => '']]];
        $this->expectException(InvalidConfigurationException::class);
        $processor->processConfiguration($configuration, $configs);
    }
}