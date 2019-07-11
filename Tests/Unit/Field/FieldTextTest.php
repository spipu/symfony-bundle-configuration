<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldText;

class FieldTextTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'text';
    }

    protected function getField()
    {
        return new FieldText();
    }

    protected function getDefinition(bool $required)
    {
        return new Definition('mock.test', $this->getCode(), $required, null, null, null, null);
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
        return \Symfony\Component\Form\Extension\Core\Type\TextareaType::class;
    }
}
