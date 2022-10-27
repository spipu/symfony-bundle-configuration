<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldColor;

class FieldColorTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'color';
    }

    protected function getField()
    {
        return new FieldColor();
    }

    protected function getDefinition(bool $required, bool $scoped = false)
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, null, null, null);
    }

    protected function getGoodValue()
    {
        return '#00aaCC';
    }

    protected function getBadValue()
    {
        return 'ayh56';
    }

    protected function getEmptyValue()
    {
        return '';
    }

    protected function getFieldClassName()
    {
        return \Symfony\Component\Form\Extension\Core\Type\ColorType::class;
    }
}
