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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class SpipuConfigurationConfiguration implements ConfigurationInterface
{
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

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('spipu_configuration');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
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

        return $treeBuilder;
    }
}
