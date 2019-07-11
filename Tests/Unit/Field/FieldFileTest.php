<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldFile;
use Spipu\ConfigurationBundle\Field\FieldString;

class FieldFileTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'file';
    }

    protected function getField()
    {
        return new FieldFile();
    }

    protected function getDefinition(bool $required)
    {
        return new Definition('mock.test', $this->getCode(), $required, null, null, 'test', null);
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
        return \Symfony\Component\Form\Extension\Core\Type\FileType::class;
    }
}
