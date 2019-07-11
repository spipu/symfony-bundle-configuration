<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldInteger;

class FieldIntegerTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'integer';
    }

    protected function getField()
    {
        return new FieldInteger();
    }

    protected function getDefinition(bool $required)
    {
        return new Definition('mock.test', $this->getCode(), $required, null, null, null, null);
    }

    protected function getGoodValue()
    {
        return 1;
    }

    protected function getBadValue()
    {
        return 'a';
    }

    protected function getEmptyValue()
    {
        return 0;
    }

    protected function getFieldClassName()
    {
        return \Symfony\Component\Form\Extension\Core\Type\IntegerType::class;
    }

    public function testValidateValueOther()
    {
        $field = $this->getField();

        $definition = $this->getDefinition(false);
        $this->assertSame(0, $field->validateValue($definition, 0));
        $this->assertSame(1, $field->validateValue($definition, 1));

        $this->assertSame(0, $field->validateValue($definition, '0'));
        $this->assertSame(1, $field->validateValue($definition, '1'));

        $this->assertSame(0, $field->validateValue($definition, 0.));
        $this->assertSame(1, $field->validateValue($definition, 1.));
    }
}
