<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldUrl;

class FieldUrlTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'url';
    }

    protected function getField()
    {
        return new FieldUrl();
    }

    protected function getDefinition(bool $required)
    {
        return new Definition('mock.test', $this->getCode(), $required, null, null, null, null);
    }

    protected function getGoodValue()
    {
        return 'http://test.fr/';
    }

    protected function getBadValue()
    {
        return 'bad_url !!!';
    }

    protected function getEmptyValue()
    {
        return '';
    }

    protected function getFieldClassName()
    {
        return \Symfony\Component\Form\Extension\Core\Type\UrlType::class;
    }
}
