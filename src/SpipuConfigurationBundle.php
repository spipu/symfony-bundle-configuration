<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\ConfigurationBundle;

use Spipu\ConfigurationBundle\Service\RoleDefinition;
use Spipu\CoreBundle\AbstractBundle;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;
use Spipu\UiBundle\Form\Options\BooleanStatus;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class SpipuConfigurationBundle extends AbstractBundle
{
    protected string $extensionAlias = 'spipu_configuration';

    /**
     * @return string[]
     */
    public function getAvailableTypes(): array
    {
        return [
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
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->normalizeKeys(true)
            ->useAttributeAsKey('code')
            ->arrayPrototype()
                ->children()
                    ->enumNode('type')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->values($this->getAvailableTypes())
                    ->end()
                    ->scalarNode('options')
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('file_type')
                        ->beforeNormalization()->castToArray()->end()
                        ->requiresAtLeastOneElement()
                        ->scalarPrototype()->end()
                    ->end()
                    ->booleanNode('required')
                        ->isRequired()
                        ->defaultFalse()
                    ->end()
                    ->booleanNode('scoped')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('default')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('unit')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('help')
                        ->defaultNull()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        parent::loadExtension($config, $container, $builder);

        foreach ($config as $code => $configValues) {
            $config[$code] = $this->prepareConfig($configValues, $code);
        }

        ksort($config);

        $builder->setParameter('spipu_configuration', $config);
    }

    /**
     * @param array $config
     * @param string $code
     * @return array
     * @SuppressWarnings(PMD.CyclomaticComplexity)
     * @SuppressWarnings(PMD.NPathComplexity)
     */
    private function prepareConfig(array $config, string $code): array
    {
        $defaultValues = [
            'code'      => $code,
            'default'   => null,
            'file_type' => [],
            'help'      => null,
            'options'   => null,
            'scoped'    => false,
            'unit'      => null,
        ];

        $config = array_merge($defaultValues, $config);

        if (!is_array($config['file_type'])) {
            $config['file_type'] = [$config['file_type']];
        }

        // Select => Options.
        if ($config['type'] !== 'select' && $config['options'] !== null) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Unauthorized options for type "%s" under "configurations.%s"',
                    $config['type'],
                    $config['code']
                )
            );
        }
        if ($config['type'] === 'select' &&  $config['options'] === null) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Missing options for type "%s" under "configurations.%s"',
                    $config['type'],
                    $config['code']
                )
            );
        }

        // File => FileType.
        if ($config['type'] !== 'file' && !empty($config['file_type'])) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Unauthorized file_type for type "%s" under "configurations.%s"',
                    $config['type'],
                    $config['code']
                )
            );
        }

        // Password or Encrypted => no default value.
        if (in_array($config['type'], ['password', 'encrypted'], true) && $config['default'] !== null) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Unauthorized default value for type "%s" under "configurations.%s"',
                    $config['type'],
                    $config['code']
                )
            );
        }

        if ($config['type'] === 'boolean') {
            $config['options'] = BooleanStatus::class;
        }

        ksort($config);

        return $config;
    }

    public function getRolesHierarchy(): RoleDefinitionInterface
    {
        return new RoleDefinition();
    }
}
