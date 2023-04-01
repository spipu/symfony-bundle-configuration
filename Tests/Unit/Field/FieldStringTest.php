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

        $formField = $field->getFormField($definition, 'default', 'Default');
        $this->assertSame(1, $formField->getPosition());

        $formField = $field->getFormField($definition, 'global', 'Global');
        $this->assertSame(2, $formField->getPosition());

        $positions = [
            $field->getFormField($definition, 'default', 'Default')->getPosition(),
            $field->getFormField($definition, 'global', 'Global')->getPosition(),
            $field->getFormField($definition, 'aaa', 'aaa')->getPosition(),
            $field->getFormField($definition, 'aaz', 'aaz')->getPosition(),
            $field->getFormField($definition, 'aba', 'aba')->getPosition(),
            $field->getFormField($definition, 'zaa', 'zaa')->getPosition(),
        ];

        $this->assertGreaterThan($positions[0], $positions[1]);
        $this->assertGreaterThan($positions[1], $positions[2]);
        $this->assertGreaterThan($positions[2], $positions[3]);
        $this->assertGreaterThan($positions[3], $positions[4]);
        $this->assertGreaterThan($positions[4], $positions[5]);
    }
}
