<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Ui;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Ui\ConfigurationGrid;
use Spipu\UiBundle\Entity\Grid;
use Spipu\UiBundle\Form\Options\YesNo;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;

class ConfigurationGridTest extends TestCase
{
    /**
     * @return ConfigurationGrid
     */
    public static function getGrid()
    {
        $yesNo =  new YesNo();
        $grid = new ConfigurationGrid($yesNo);

        return $grid;
    }

    public function testGrid()
    {
        $grid = self::getGrid();
        $this->assertInstanceOf(GridDefinitionInterface::class, $grid);

        $grid->setShowNeededRole('ACL_TO_EDIT');
        $definition = $grid->getDefinition();
        $this->assertInstanceOf(Grid\Grid::class, $definition);

        $this->assertSame('configuration', $definition->getCode());
        $this->assertSame(
            \Spipu\ConfigurationBundle\Ui\Grid\DataProvider::class,
            $definition->getDataProviderServiceName()
        );

        $column = $definition->getColumn('code');
        $this->assertInstanceOf(Grid\Column::class, $column);
        $this->assertSame(Grid\ColumnType::TYPE_TEXT, $column->getType()->getType());

        $column = $definition->getColumn('value');
        $this->assertInstanceOf(Grid\Column::class, $column);
        $this->assertSame(Grid\ColumnType::TYPE_TEXT, $column->getType()->getType());

        $column = $definition->getColumn('type');
        $this->assertInstanceOf(Grid\Column::class, $column);
        $this->assertSame(Grid\ColumnType::TYPE_TEXT, $column->getType()->getType());

        $column = $definition->getColumn('required');
        $this->assertInstanceOf(Grid\Column::class, $column);
        $this->assertSame(Grid\ColumnType::TYPE_SELECT, $column->getType()->getType());

        $action = $definition->getRowAction('edit');
        $this->assertInstanceOf(Grid\Action::class, $action);
        $this->assertSame('spipu_configuration_admin_edit', $action->getRouteName());
        $this->assertSame('ACL_TO_EDIT', $action->getNeededRole());
    }
}
