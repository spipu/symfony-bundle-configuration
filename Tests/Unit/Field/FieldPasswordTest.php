<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldPassword;

class FieldPasswordTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'password';
    }

    protected function getField()
    {
        return new FieldPassword();
    }

    protected function getDefinition(bool $required, bool $scoped = false)
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, 'test', null, null);
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
        return \Symfony\Component\Form\Extension\Core\Type\PasswordType::class;
    }
}
