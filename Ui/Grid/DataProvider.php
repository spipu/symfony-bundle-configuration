<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Ui\Grid;

use Spipu\ConfigurationBundle\Service\Manager;
use Spipu\UiBundle\Entity\EntityInterface;
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
     * @return bool
     * @throws \Exception
     */
    private function loadItems(): bool
    {
        if (is_array($this->items)) {
            return false;
        }

        $this->validate();

        $this->items = [];

        foreach ($this->manager->getDefinitions() as $definition) {
            $item = new Entity(
                $definition->getCode(),
                $definition->getType(),
                $definition->isRequired(),
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

        return true;
    }

    /**
     * @param Entity $item
     * @return bool
     */
    private function filterItem(Entity $item): bool
    {
        foreach ($this->getFilters() as $filterField => $filterValue) {
            $filterValue = (string) $filterValue;
            $itemValue = (string) $item->{'get' . ucfirst($filterField)}();
            if (strpos($itemValue, $filterValue) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getNbTotalRows(): int
    {
        $this->loadItems();

        return count($this->items);
    }

    /**
     * @return EntityInterface[]
     * @throws \Exception
     */
    public function getPageRows(): array
    {
        $this->loadItems();

        return $this->items;
    }
}
