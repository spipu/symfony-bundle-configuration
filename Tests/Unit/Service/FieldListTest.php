<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Service;

use Spipu\UiBundle\Entity\Form;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Field;
use Spipu\ConfigurationBundle\Service\FieldList;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;

class FieldListTest extends TestCase
{
    public function testBase()
    {
        $fieldList = new FieldList([]);

        $this->assertInstanceOf(Field\FieldInterface::class, $fieldList);
        $this->assertSame('list', $fieldList->getCode());
    }

    public function testEmpty()
    {
        $list = [];

        $fieldList = new FieldList($list);

        $this->assertSame($list, $fieldList->getFields());
    }

    public function testBad()
    {
        $list = [
            new \stdClass(),
        ];

        $this->expectException(ConfigurationException::class);
        new FieldList($list);
    }

    public function testGood()
    {
        $list = [
            'string' => new Field\FieldString(),
            'text'   => new Field\FieldText(),
            'url'    => new Field\FieldUrl(),
        ];

        $fieldList = new FieldList(array_values($list));

        $this->assertSame($list, $fieldList->getFields());

        $manager = SpipuConfigurationMock::getManager($this);

        foreach ($list as $code => $field) {
            $definition = $manager->getDefinition('mock.' . $code);

            $this->assertSame($field, $fieldList->getField($definition));
        }
    }

    public function testCallBack()
    {
        $formField = new Form\Field('value', 'good', 10, []);

        $fieldGoodMock = $this->createMock(Field\FieldInterface::class);
        $fieldGoodMock->expects($this->once())->method('getCode')->willReturn('good');
        $fieldGoodMock->expects($this->once())->method('prepareValue')->willReturnArgument(1);
        $fieldGoodMock->expects($this->once())->method('validateValue')->willReturnArgument(1);
        $fieldGoodMock->expects($this->once())->method('getFormField')->willReturn($formField);

        $fieldBadMock = $this->createMock(Field\FieldInterface::class);
        $fieldBadMock->expects($this->once())->method('getCode')->willReturn('bad');
        $fieldBadMock->expects($this->never())->method('prepareValue');
        $fieldBadMock->expects($this->never())->method('validateValue');
        $fieldBadMock->expects($this->never())->method('getFormField');

        $manager = SpipuConfigurationMock::getManager($this, ['mock.good' => 'good', 'mock.bad' => 'bad']);
        $definition = $manager->getDefinition('mock.good');

        $list = [$fieldGoodMock, $fieldBadMock];

        $fieldList = new FieldList($list);
        $this->assertSame('value', $fieldList->prepareValue($definition, 'value'));
        $this->assertSame('value', $fieldList->validateValue($definition, 'value'));
        $this->assertSame($formField, $fieldList->getFormField($definition));
    }
}
