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
use Spipu\UiBundle\Form\Options\YesNo as OptionsYesNo;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;

class ConfigurationGrid implements GridDefinitionInterface
{
    private OptionsYesNo $optionsYesNo;
    private ?Grid\Grid $definition = null;
    private ?string $currentScope = null;
    private string $showNeededRole = 'NOT_ALLOWED';
    private string $editRouteName = 'spipu_configuration_admin_edit';
    private array $editRouteParams = [];

    public function __construct(
        OptionsYesNo $optionsYesNo
    ) {
        $this->optionsYesNo = $optionsYesNo;
    }

    public function getDefinition(): Grid\Grid
    {
        if (!$this->definition) {
            $this->prepareGrid();
        }

        return $this->definition;
    }

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
                    ->setFilter((new Grid\ColumnFilter(true, true)))
                    ->setOptions(['td-css-class' => 'pl-4 text-left w-25'])
            )
            ->addColumn(
                (new Grid\Column('value', 'spipu.configuration.field.value', 'value', 20))
                    ->setType(
                        (new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT))
                            ->setTemplateField('@SpipuConfiguration/grid/field/value.html.twig')
                    )
                    ->setFilter((new Grid\ColumnFilter(true)))
                    ->setOptions(['td-css-class' => 'text-left w-50'])
            )
            ->addColumn(
                (new Grid\Column('type', 'spipu.configuration.field.type', 'type', 30))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT)))
            )
            ->addColumn(
                (new Grid\Column('required', 'spipu.configuration.field.required', 'required', 40))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_SELECT))->setOptions($this->optionsYesNo))
                    ->setFilter((new Grid\ColumnFilter(true)))
            )
            ->addColumn(
                (new Grid\Column('scoped', 'spipu.configuration.field.scoped', 'scoped', 50))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_SELECT))->setOptions($this->optionsYesNo))
                    ->setFilter((new Grid\ColumnFilter(true)))
            )
            ->addRowAction(
                (new Grid\Action(
                    'edit',
                    'spipu.ui.action.edit',
                    10,
                    $this->editRouteName,
                    $this->editRouteParams + ['scopeCode' => $this->currentScope]
                ))
                    ->setCssClass('success')
                    ->setIcon('edit')
                    ->setNeededRole($this->showNeededRole)
            )
        ;
    }

    public function setCurrentScope(?string $currentScope): ConfigurationGrid
    {
        $this->currentScope = $currentScope;
        return $this;
    }

    public function setShowNeededRole(string $role): ConfigurationGrid
    {
        $this->showNeededRole = $role;
        return $this;
    }

    public function setEditRoute(string $routeName, array $routeParams): ConfigurationGrid
    {
        $this->editRouteName = $routeName;
        $this->editRouteParams = $routeParams;

        return $this;
    }
}
