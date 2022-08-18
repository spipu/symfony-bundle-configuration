<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Ui\Grid;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;
use Spipu\ConfigurationBundle\Tests\Unit\Ui\ConfigurationGridTest;
use Spipu\ConfigurationBundle\Ui\Grid\DataProvider;
use Spipu\ConfigurationBundle\Ui\Grid\Entity;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\DataProviderInterface;
use Spipu\UiBundle\Service\Ui\Grid\GridRequest;
use Spipu\UiBundle\Tests\SpipuUiMock;

class DataProviderTest extends TestCase
{
    public function testDataProviderNoFilter()
    {
        $filters = [];
        $expected = [
            'mock.string' => 'mock.string',
            'mock.text' => 'mock.text',
            'mock.url' => 'mock.url',
        ];

        $this->makeTest($filters, $expected);
    }

    public function testDataProviderFilterCode()
    {
        $filters = [GridRequest::KEY_FILTERS => ['code'  => 't']];

        $expected = [
            'mock.string' => 'mock.string',
            'mock.text' => 'mock.text',
        ];

        $this->makeTest($filters, $expected);
    }

    public function testDataProviderFilterValue()
    {
        $filters = [GridRequest::KEY_FILTERS => ['value'  => 'url']];

        $expected = [
            'mock.url' => 'mock.url',
        ];

        $this->makeTest($filters, $expected);
    }

    public function testDataProviderClone()
    {
        $manager = SpipuConfigurationMock::getManager($this);

        $manager->expects($this->exactly(2))->method('getDefinitions');

        $grid = ConfigurationGridTest::getGrid();
        $request = SpipuUiMock::getGridRequest($this, $grid->getDefinition(), []);
        $request->setRoute('mock_route', []);
        $request->prepare();

        $dataProvider = new DataProvider($manager);
        $dataProvider->setGridDefinition($grid->getDefinition());
        $dataProvider->setGridRequest($request);

        $this->assertSame(3, $dataProvider->getNbTotalRows());
        $this->assertSame(3, $dataProvider->getNbTotalRows());

        $clone = clone $dataProvider;
        $clone->setGridDefinition($grid->getDefinition());
        $clone->setGridRequest($request);

        $this->assertSame(3, $clone->getNbTotalRows());
        $this->assertSame(3, $clone->getNbTotalRows());
    }

    private function makeTest(array $filters, array $expected)
    {
        $grid = ConfigurationGridTest::getGrid();

        $request = SpipuUiMock::getGridRequest($this, $grid->getDefinition(), $filters);
        $request->setRoute('mock_route', []);
        $request->prepare();

        $manager = SpipuConfigurationMock::getManager($this);

        $dataProvider = new DataProvider($manager);
        $dataProvider->setGridDefinition($grid->getDefinition());
        $dataProvider->setGridRequest($request);

        $this->assertInstanceOf(DataProviderInterface::class, $dataProvider);

        /** @var Entity[] $rows */
        $rows = $dataProvider->getPageRows();
        $this->assertSame(count($expected), $dataProvider->getNbTotalRows());
        $this->assertSame(count($expected), count($rows));

        $values = [];
        foreach ($rows as $row) {
            $values[$row->getCode()] = $row->getValue();
        }

        $this->assertSame($expected, $values);
    }
}
