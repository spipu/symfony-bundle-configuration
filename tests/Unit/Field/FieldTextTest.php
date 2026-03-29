<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldText;

class FieldTextTest extends AbstractFieldTest
{
    protected function getCode(): string
    {
        return 'text';
    }

    protected function getField(): object
    {
        return new FieldText();
    }

    protected function getDefinition(bool $required, bool $scoped = false): Definition
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, null, null, null);
    }

    protected function getGoodValue(): mixed
    {
        return 'good';
    }

    protected function getBadValue(): mixed
    {
        return null;
    }

    protected function getEmptyValue(): mixed
    {
        return '';
    }

    protected function getFieldClassName(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\TextareaType::class;
    }
}
