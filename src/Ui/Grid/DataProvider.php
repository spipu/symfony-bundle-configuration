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

namespace Spipu\ConfigurationBundle\Ui\Grid;

use Spipu\ConfigurationBundle\Service\ConfigurationManager as Manager;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    private Manager $manager;

    /**
     * @var Entity[]|null
     */
    private ?array $items = null;
    private ?string $currentScope = null;
    private ?array $allowedCodes = null;

    public function __construct(
        Manager $manager
    ) {
        $this->manager = $manager;
    }

    public function __clone()
    {
        $this->items = null;

        parent::__clone();
    }

    private function loadItems(): void
    {
        if (is_array($this->items)) {
            return;
        }

        $this->validate();

        $this->items = [];

        foreach ($this->manager->getDefinitions() as $definition) {
            if ($this->allowedCodes !== null && !in_array($definition->getCode(), $this->allowedCodes)) {
                continue;
            }

            if ($this->currentScope !== null && !$definition->isScoped()) {
                continue;
            }

            $item = new Entity(
                $definition->getCode(),
                $definition->getType(),
                $definition->isRequired(),
                $definition->isScoped(),
                $definition->getDefault(),
                $definition->getOptions(),
                $definition->getUnit(),
                $definition->getHelp(),
                $definition->getFileTypes()
            );

            $item->setValue($this->manager->get($item->getCode(), $this->currentScope));

            if ($this->filterItem($item)) {
                $this->items[] = $item;
            }
        }
    }

    private function filterItem(Entity $item): bool
    {
        foreach ($this->getFilters() as $filterField => $filterValue) {
            $filterValue = mb_strtolower((string) $filterValue);
            $itemValue = $this->getItemValue($item, (string) $filterField);

            if (!str_contains($itemValue, $filterValue)) {
                return false;
            }
        }

        if ($this->request->getQuickSearchField() && $this->request->getQuickSearchValue()) {
            $filterValue = mb_strtolower($this->request->getQuickSearchValue());
            $itemValue = $this->getItemValue($item, $this->request->getQuickSearchField());

            if (!str_starts_with($itemValue, $filterValue)) {
                return false;
            }
        }

        return true;
    }

    private function getGetterName(Entity $item, string $fieldName): string
    {
        $methods = [
            'get' . ucfirst($fieldName),
            'is' . ucfirst($fieldName),
            $fieldName
        ];

        $found = count($methods) - 1;
        foreach ($methods as $key => $method) {
            if (method_exists($item, $method)) {
                $found = $key;
            }
        }

        return $methods[$found];
    }

    public function getNbTotalRows(): int
    {
        $this->loadItems();

        return count($this->items);
    }

    public function getPageRows(): array
    {
        $this->loadItems();

        return $this->items;
    }

    private function getItemValue(Entity $item, string $filterField): string
    {
        $method = $this->getGetterName($item, $filterField);
        $itemValue = $item->{$method}();

        if ($itemValue === true) {
            $itemValue = '1';
        }

        if ($itemValue === false) {
            $itemValue = '0';
        }
        return mb_strtolower((string) $itemValue);
    }

    public function setCurrentScope(?string $scopeCode): void
    {
        $this->currentScope = $scopeCode;
    }

    public function setAllowedCodes(?array $allowedCodes): void
    {
        $this->allowedCodes = $allowedCodes;
    }
}
