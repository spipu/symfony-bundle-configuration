<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldInteger;

class FieldIntegerTest extends AbstractFieldTest
{
    protected function getCode(): string
    {
        return 'integer';
    }

    protected function getField(): object
    {
        return new FieldInteger();
    }

    protected function getDefinition(bool $required, bool $scoped = false): Definition
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, null, null, null);
    }

    protected function getGoodValue(): mixed
    {
        return 1;
    }

    protected function getBadValue(): mixed
    {
        return 'a';
    }

    protected function getEmptyValue(): mixed
    {
        return 0;
    }

    protected function getFieldClassName(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\IntegerType::class;
    }

    public function testValidateValueOther(): void
    {
        $field = $this->getField();

        $definition = $this->getDefinition(false);
        $this->assertSame(0, $field->validateValue($definition, 0));
        $this->assertSame(1, $field->validateValue($definition, 1));

        $this->assertSame(0, $field->validateValue($definition, '0'));
        $this->assertSame(1, $field->validateValue($definition, '1'));

        $this->assertSame(0, $field->validateValue($definition, 0.));
        $this->assertSame(1, $field->validateValue($definition, 1.));
    }
}
