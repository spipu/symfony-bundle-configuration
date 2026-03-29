<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\UiBundle\Entity\Form\Field;

abstract class AbstractFieldTest extends TestCase
{
    abstract protected function getCode(): string;

    abstract protected function getField(): object;

    abstract protected function getDefinition(bool $required, bool $scoped = false): Definition;

    abstract protected function getGoodValue(): mixed;

    abstract protected function getEmptyValue(): mixed;

    abstract protected function getBadValue(): mixed;

    abstract protected function getFieldClassName(): string;

    public function testFieldCode(): void
    {
        $field = $this->getField();
        $this->assertSame($this->getCode(), $field->getCode());
    }

    public function testFieldPrepareValue(): void
    {
        $field = $this->getField();

        $definition = $this->getDefinition(false);
        $this->assertSame(null, $field->prepareValue($definition, null));
        $this->assertSame(null, $field->prepareValue($definition, ''));
        $this->assertSame($this->getGoodValue(), $field->prepareValue($definition, $this->getGoodValue()));

        $definition = $this->getDefinition(true);
        $this->assertSame($this->getEmptyValue(), $field->prepareValue($definition, null));
        $this->assertSame($this->getEmptyValue(), $field->prepareValue($definition, ''));
        $this->assertSame($this->getGoodValue(), $field->prepareValue($definition, $this->getGoodValue()));
    }

    public function testFieldValidateValueNotRequired(): void
    {
        $field = $this->getField();

        $definition = $this->getDefinition(false);
        $this->assertSame(null, $field->validateValue($definition, null));
        $this->assertSame(null, $field->validateValue($definition, ''));
        $this->assertSame($this->getGoodValue(), $field->validateValue($definition, $this->getGoodValue()));
    }

    public function testFieldValidateValueRequired(): void
    {
        $field = $this->getField();

        $definition = $this->getDefinition(true);
        $this->assertSame($this->getGoodValue(), $field->validateValue($definition, $this->getGoodValue()));

        if ($this->getBadValue() !== null) {
            $this->expectException(ConfigurationException::class);
            $field->validateValue($definition, $this->getBadValue());
        }
    }

    public function testFieldValidateValueRequiredKoNull(): void
    {
        $field = $this->getField();
        $definition = $this->getDefinition(true);

        $this->expectException(ConfigurationException::class);
        $field->validateValue($definition, null);
    }

    public function testFieldValidateValueRequiredKoEmpty(): void
    {
        $field = $this->getField();
        $definition = $this->getDefinition(true);

        $this->expectException(ConfigurationException::class);
        $field->validateValue($definition, '');
    }

    public function testFormFieldRequired(): void
    {
        $field = $this->getField();
        $definition = $this->getDefinition(true);

        $formField = $field->getFormField($definition, 'global', 'Global');
        $this->assertInstanceOf(Field::class, $formField);
        $this->assertSame('value_global', $formField->getCode());
        $this->assertSame($this->getFieldClassName(), $formField->getType());
        $this->assertSame(10, $formField->getPosition());
        $this->assertSame('Global', $formField->getOptions()['label']);
        $this->assertTrue($formField->getOptions()['required']);
    }

    public function testFormFieldNotRequired(): void
    {
        $field = $this->getField();
        $definition = $this->getDefinition(false);

        $formField = $field->getFormField($definition, 'global', 'Global');
        $this->assertInstanceOf(Field::class, $formField);
        $this->assertSame('value_global', $formField->getCode());
        $this->assertSame($this->getFieldClassName(), $formField->getType());
        $this->assertSame(10, $formField->getPosition());
        $this->assertSame('Global', $formField->getOptions()['label']);
        $this->assertFalse($formField->getOptions()['required']);
    }
}
