<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldEmail;

class FieldEmailTest extends AbstractFieldTest
{
    protected function getCode(): string
    {
        return 'email';
    }

    protected function getField(): object
    {
        return new FieldEmail();
    }

    protected function getDefinition(bool $required, bool $scoped = false): Definition
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, null, null, null);
    }

    protected function getGoodValue(): mixed
    {
        return 'test@domain.fr,mock@domain.fr';
    }

    protected function getBadValue(): mixed
    {
        return 'test@domain.fr,bad_email';
    }

    protected function getEmptyValue(): mixed
    {
        return '';
    }

    protected function getFieldClassName(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\EmailType::class;
    }
}
