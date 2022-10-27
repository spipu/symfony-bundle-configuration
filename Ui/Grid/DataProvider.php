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

use Exception;
use Spipu\ConfigurationBundle\Service\ConfigurationManager as Manager;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var Entity[]
     */
    private $items;

    /**
     * Doctrine constructor.
     * @param Manager $manager
     */
    public function __construct(
        Manager $manager
    ) {
        $this->manager = $manager;
    }

    /**
     * need by Spipu Ui
     *
     * @return void
     */
    public function __clone()
    {
        $this->items = null;

        parent::__clone();
    }

    /**
     * @return void
     * @throws Exception
     */
    private function loadItems(): void
    {
        if (is_array($this->items)) {
            return;
        }

        $this->validate();

        $this->items = [];

        foreach ($this->manager->getDefinitions() as $definition) {
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

            $item->setValue($this->manager->get($item->getCode()));

            if ($this->filterItem($item)) {
                $this->items[] = $item;
            }
        }
    }

    /**
     * @param Entity $item
     * @return bool
     * @throws GridException
     */
    private function filterItem(Entity $item): bool
    {
        foreach ($this->getFilters() as $filterField => $filterValue) {
            $filterValue = mb_strtolower((string) $filterValue);
            $itemValue = $this->getItemValue($item, (string) $filterField);

            if (strpos($itemValue, $filterValue) === false) {
                return false;
            }
        }

        if ($this->request->getQuickSearchField() && $this->request->getQuickSearchValue()) {
            $filterValue = mb_strtolower($this->request->getQuickSearchValue());
            $itemValue = $this->getItemValue($item, $this->request->getQuickSearchField());

            if (strpos($itemValue, $filterValue) !== 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Entity $item
     * @param string $fieldName
     * @return string
     */
    private function getGetterName(Entity $item, string $fieldName): string
    {
        $methods = [
            'get' . ucfirst($fieldName),
            'is' . ucfirst($fieldName),
        ];

        foreach ($methods as $method) {
            if (method_exists($item, $method)) {
                return $method;
            }
        }

        return $fieldName;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getNbTotalRows(): int
    {
        $this->loadItems();

        return count($this->items);
    }

    /**
     * @return EntityInterface[]
     * @throws Exception
     */
    public function getPageRows(): array
    {
        $this->loadItems();

        return $this->items;
    }

    /**
     * @param Entity $item
     * @param string $filterField
     * @return string
     */
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
}
