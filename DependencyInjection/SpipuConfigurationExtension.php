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

namespace Spipu\ConfigurationBundle\DependencyInjection;

use Exception;
use Spipu\ConfigurationBundle\Service\RoleDefinition;
use Spipu\CoreBundle\DependencyInjection\RolesHierarchyExtensionExtensionInterface;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;
use Spipu\UiBundle\Form\Options\BooleanStatus;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SpipuConfigurationExtension extends Extension implements RolesHierarchyExtensionExtensionInterface
{
    public function getAlias(): string
    {
        return 'spipu_configuration';
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws Exception
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $configs = $this->processConfiguration($configuration, $configs);

        foreach ($configs as $code => $config) {
            $configs[$code] = $this->prepareConfig($config, $code);
        }

        ksort($configs);

        $container->setParameter('spipu_configuration', $configs);
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
            'options'   => null,
            'unit'      => null,
            'help'      => null,
            'file_type' => [],
        ];

        $config = array_merge($defaultValues, $config);

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

    /**
     * Get the configuration to use
     * @param array $config
     * @param ContainerBuilder $container
     * @return ConfigurationInterface
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new SpipuConfigurationConfiguration();
    }

    public function getRolesHierarchy(): RoleDefinitionInterface
    {
        return new RoleDefinition();
    }
}
