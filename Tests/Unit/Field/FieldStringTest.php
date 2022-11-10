<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldString;

class FieldStringTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'string';
    }

    protected function getField()
    {
        return new FieldString();
    }

    protected function getDefinition(bool $required, bool $scoped = false)
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, 'test', 'help', null);
    }

    protected function getGoodValue()
    {
        return 'good';
    }

    protected function getBadValue()
    {
        return null;
    }

    protected function getEmptyValue()
    {
        return '';
    }

    protected function getFieldClassName()
    {
        return \Symfony\Component\Form\Extension\Core\Type\TextType::class;
    }

    public function testFormFieldOther()
    {
        $field = $this->getField();
        $definition = $this->getDefinition(true);

        $formField = $field->getFormField($definition, 'global', 'Global');
        $this->assertSame('Unit: test | help', $formField->getOptions()['help']);
    }
}
