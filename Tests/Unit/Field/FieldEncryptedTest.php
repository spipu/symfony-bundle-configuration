<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldEncrypted;

class FieldEncryptedTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'encrypted';
    }

    protected function getField()
    {
        return new FieldEncrypted();
    }

    protected function getDefinition(bool $required, bool $perScope = false)
    {
        return new Definition('mock.test', $this->getCode(), $required, $perScope, null, null, 'test', null, null);
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
