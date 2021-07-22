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

namespace Spipu\ConfigurationBundle\Ui;

use Spipu\ConfigurationBundle\Ui\Grid\DataProvider;
use Spipu\UiBundle\Entity\Grid;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Form\Options\YesNo as OptionsYesNo;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;

class ConfigurationGrid implements GridDefinitionInterface
{
    /**
     * @var OptionsYesNo
     */
    private $optionsYesNo;

    /**
     * UserGrid constructor.
     * @param OptionsYesNo $optionsYesNo
     */
    public function __construct(
        OptionsYesNo $optionsYesNo
    ) {
        $this->optionsYesNo = $optionsYesNo;
    }

    /**
     * @var Grid\Grid
     */
    private $definition;

    /**
     * @return Grid\Grid
     * @throws GridException
     */
    public function getDefinition(): Grid\Grid
    {
        if (!$this->definition) {
            $this->prepareGrid();
        }

        return $this->definition;
    }

    /**
     * @return void
     * @throws GridException
     */
    private function prepareGrid(): void
    {
        $this->definition = (new Grid\Grid('configuration'))
            ->setDataProviderServiceName(DataProvider::class)
            ->setPrimaryKey('code', 'code')
            ->setTemplateRow('@SpipuConfiguration/grid/row.html.twig')
            ->setOptions(['table-css-class' => 'table table-hover table-sm'])
            ->addColumn(
                (new Grid\Column('code', 'spipu.configuration.field.code', 'code', 10))
                    ->setType(
                        (new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT))
                            ->setTemplateField('@SpipuConfiguration/grid/field/code.html.twig')
                    )
                    ->setFilter((new Grid\ColumnFilter(true)))
                    ->setOptions(['td-css-class' => 'pl-4 text-left'])
            )
            ->addColumn(
                (new Grid\Column('value', 'spipu.configuration.field.value', 'value', 20))
                    ->setType(
                        (new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT))
                            ->setTemplateField('@SpipuConfiguration/grid/field/value.html.twig')
                    )
                    ->setFilter((new Grid\ColumnFilter(true)))
            )
            ->addColumn(
                (new Grid\Column('type', 'spipu.configuration.field.type', 'type', 30))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT)))
            )
            ->addColumn(
                (new Grid\Column('required', 'spipu.configuration.field.required', 'required', 40))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_SELECT))->setOptions($this->optionsYesNo))
            )
            ->addRowAction(
                (new Grid\Action('edit', 'spipu.ui.action.edit', 10, 'spipu_configuration_admin_edit'))
                    ->setCssClass('success')
                    ->setIcon('edit')
                    ->setNeededRole('ROLE_ADMIN_MANAGE_CONFIGURATION_EDIT')
            )
        ;
    }
}
