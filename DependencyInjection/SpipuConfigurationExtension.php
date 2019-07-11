<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\DependencyInjection;

use Spipu\ConfigurationBundle\Service\RoleDefinition;
use Spipu\CoreBundle\DependencyInjection\RolesHierarchiExtensionExtensionInterface;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SpipuConfigurationExtension extends Extension implements RolesHierarchiExtensionExtensionInterface
{
    /**
     * Get the alias in config file
     * @return string
     */
    public function getAlias(): string
    {
        return 'spipu_configuration';
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws \Exception
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

        if ($config['type'] === 'boolean') {
            $config['options'] = \Spipu\UiBundle\Form\Options\BooleanStatus::class;
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

    /**
     * @return RoleDefinitionInterface
     */
    public function getRolesHierarchy(): RoleDefinitionInterface
    {
        return new RoleDefinition();
    }
}
