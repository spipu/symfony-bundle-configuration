<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldFloat;

class FieldFloatTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'float';
    }

    protected function getField()
    {
        return new FieldFloat();
    }

    protected function getDefinition(bool $required, bool $perScope = false)
    {
        return new Definition('mock.test', $this->getCode(), $required, $perScope, null, null, null, null, null);
    }

    protected function getGoodValue()
    {
        return 1.;
    }

    protected function getBadValue()
    {
        return 'a';
    }

    protected function getEmptyValue()
    {
        return 0.;
    }

    protected function getFieldClassName()
    {
        return \Symfony\Component\Form\Extension\Core\Type\NumberType::class;
    }

    public function testValidateValueOther()
    {
        $field = $this->getField();

        $definition = $this->getDefinition(false);
        $this->assertSame(0., $field->validateValue($definition, 0.));
        $this->assertSame(1., $field->validateValue($definition, 1.));

        $this->assertSame(0., $field->validateValue($definition, '0'));
        $this->assertSame(1., $field->validateValue($definition, '1'));

        $this->assertSame(0., $field->validateValue($definition, 0));
        $this->assertSame(1., $field->validateValue($definition, 1));
    }
}
