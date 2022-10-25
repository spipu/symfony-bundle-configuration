<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spipu\ConfigurationBundle\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Service\ConfigurationManager;

class SpipuConfigurationMock extends TestCase
{
    /**
     * @param TestCase $testCase
     * @param array|null $definition
     * @param array $values
     * @return MockObject|ConfigurationManager
     */
    public static function getManager(TestCase $testCase, array $definition = null, array $values = [])
    {
        if ($definition === null) {
            $definition = [
                'mock.string' => 'string',
                'mock.text' => 'text',
                'mock.url' => 'url',
            ];
        }

        $definitionMap = [];
        foreach ($definition as $key => $type) {
            $definitionMap[$key] = new Definition($key, $type, true, '', null, null, null, null);
        }

        $service = $testCase->createMock(ConfigurationManager::class);

        $service
            ->method('getFileUrl')
            ->willReturn('folder/mock/');

        if (count($values) === 0) {
            $service
                ->method('get')
                ->will($testCase->returnArgument(0));
        } else {
            $map = [];
            foreach ($values as $key => $value) {
                $map[] = [$key, $value];
            }

            $service
                ->method('get')
                ->willReturnMap($map);
        }

        $service
            ->method('getDefinitions')
            ->willReturn($definitionMap);

        $service
            ->method('getDefinition')
            ->willReturnCallback(
                function (string $key) use ($definitionMap) {
                    return $definitionMap[$key];
                }
            );

        $service
            ->method('getField')
            ->willReturnCallback(
                function (string $key) use ($definitionMap) {
                    $className = '\Spipu\ConfigurationBundle\Field\Field' . ucfirst($definitionMap[$key]->getType());
                    return new $className();
                }
            );

        /** @var ConfigurationManager $service */
        return $service;
    }
}
