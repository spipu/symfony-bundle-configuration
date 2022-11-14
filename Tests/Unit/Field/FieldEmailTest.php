<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldEmail;

class FieldEmailTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'email';
    }

    protected function getField()
    {
        return new FieldEmail();
    }

    protected function getDefinition(bool $required, bool $scoped = false)
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, null, null, null);
    }

    protected function getGoodValue()
    {
        return 'test@domain.fr,mock@domain.fr';
    }

    protected function getBadValue()
    {
        return 'test@domain.fr,bad_email';
    }

    protected function getEmptyValue()
    {
        return '';
    }

    protected function getFieldClassName()
    {
        return \Symfony\Component\Form\Extension\Core\Type\EmailType::class;
    }
}
