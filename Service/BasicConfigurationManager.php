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

namespace Spipu\ConfigurationBundle\Service;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Exception\ConfigurationScopeException;
use Spipu\ConfigurationBundle\Field\FieldInterface;

class BasicConfigurationManager
{
    /**
     * @var FieldList
     */
    private $fieldList;

    /**
     * @var Definitions
     */
    private $definitions;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param FieldList $fieldList
     * @param Definitions $definitions
     * @param Storage $storage
     */
    public function __construct(
        FieldList $fieldList,
        Definitions $definitions,
        Storage $storage
    ) {
        $this->fieldList = $fieldList;
        $this->definitions = $definitions;
        $this->storage = $storage;
    }

    /**
     * Get the configuration definitions
     * @return Definition[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions->getAll();
    }

    /**
     * Get the configuration definition of a specific key
     * @param string $key
     * @return Definition
     * @throws ConfigurationException
     */
    public function getDefinition(string $key): Definition
    {
        return $this->definitions->get($key);
    }

    /**
     * @param string $key
     * @return FieldInterface
     * @throws ConfigurationException
     */
    public function getField(string $key): FieldInterface
    {
        $definition = $this->definitions->get($key);
        return $this->fieldList->getField($definition);
    }

    /**
     * @return array
     * @throws ConfigurationException
     */
    public function getAll(): array
    {
        return $this->storage->getAll();
    }

    /**
     * Get a configuration value
     * @param string $key
     * @param string|null $scope
     * @return mixed
     * @throws ConfigurationException
     */
    public function get(string $key, ?string $scope = null)
    {
        return $this->storage->get($key, $scope);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string|null $scope
     * @return void
     * @throws ConfigurationException
     */
    public function set(string $key, $value, ?string $scope = null): void
    {
        $this->storage->set($key, $value, $scope);
    }

    /**
     * Delete a configuration value, to restore the default or the global value
     * @param string $key
     * @param string|null $scope
     * @return void
     * @throws ConfigurationException
     */
    public function delete(string $key, ?string $scope = null): void
    {
        $this->storage->delete($key, $scope);
    }

    /**
     * @return void
     */
    public function clearCache(): void
    {
        $this->storage->cleanValues();
    }
}
