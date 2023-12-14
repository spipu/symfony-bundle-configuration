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
use Spipu\ConfigurationBundle\Field\FieldInterface;

class BasicConfigurationManager
{
    private FieldList $fieldList;
    private Definitions $definitions;
    private Storage $storage;

    public function __construct(
        FieldList $fieldList,
        Definitions $definitions,
        Storage $storage,
    ) {
        $this->fieldList = $fieldList;
        $this->definitions = $definitions;
        $this->storage = $storage;
    }

    /**
     * @return Definition[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions->getAll();
    }

    public function getDefinition(string $key): Definition
    {
        return $this->definitions->get($key);
    }

    public function getField(string $key): FieldInterface
    {
        $definition = $this->definitions->get($key);
        return $this->fieldList->getField($definition);
    }

    public function getAll(): array
    {
        return $this->storage->getAll();
    }

    public function get(string $key, ?string $scope = null): mixed
    {
        return $this->storage->get($key, $scope);
    }

    public function set(string $key, mixed $value, ?string $scope = null): void
    {
        $this->storage->set($key, $value, $scope);
    }

    public function delete(string $key, ?string $scope = null): void
    {
        $this->storage->delete($key, $scope);
    }

    public function clearCache(): void
    {
        $this->storage->cleanValues();
    }

    protected function validateScope(?string $scope): string
    {
        return $this->storage->validateScope($scope);
    }
}
