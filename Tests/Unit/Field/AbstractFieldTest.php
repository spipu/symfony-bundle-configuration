<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Field\FieldInterface;
use Spipu\UiBundle\Entity\Form\Field;

abstract class AbstractFieldTest extends TestCase
{
    abstract protected function getCode();

    /**
     * @return FieldInterface
     */
    abstract protected function getField();

    abstract protected function getDefinition(bool $required);

    abstract protected function getGoodValue();

    abstract protected function getEmptyValue();

    abstract protected function getBadValue();

    abstract protected function getFieldClassName();

    public function testFieldCode()
    {
        $field = $this->getField();
        $this->assertSame($this->getCode(), $field->getCode());
    }

    public function testFieldPrepareValue()
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

    public function testFieldValidateValueNotRequired()
    {
        $field = $this->getField();

        $definition = $this->getDefinition(false);
        $this->assertSame(null, $field->validateValue($definition, null));
        $this->assertSame(null, $field->validateValue($definition, ''));
        $this->assertSame($this->getGoodValue(), $field->validateValue($definition, $this->getGoodValue()));
    }

    public function testFieldValidateValueRequired()
    {
        $field = $this->getField();

        $definition = $this->getDefinition(true);
        $this->assertSame($this->getGoodValue(), $field->validateValue($definition, $this->getGoodValue()));

        if ($this->getBadValue() !== null) {
            $this->expectException(ConfigurationException::class);
            $field->validateValue($definition, $this->getBadValue());
        }
    }

    public function testFieldValidateValueRequiredKoNull()
    {
        $field = $this->getField();
        $definition = $this->getDefinition(true);

        $this->expectException(ConfigurationException::class);
        $field->validateValue($definition, null);
    }

    public function testFieldValidateValueRequiredKoEmpty()
    {
        $field = $this->getField();
        $definition = $this->getDefinition(true);

        $this->expectException(ConfigurationException::class);
        $field->validateValue($definition, '');
    }

    public function testFormFieldRequired()
    {
        $field = $this->getField();
        $definition = $this->getDefinition(true);

        $formField = $field->getFormField($definition, 'global', 'Global');
        $this->assertInstanceOf(Field::class, $formField);
        $this->assertSame('value_global', $formField->getCode());
        $this->assertSame($this->getFieldClassName(), $formField->getType());
        $this->assertSame(2, $formField->getPosition());
        $this->assertSame('Global', $formField->getOptions()['label']);
        $this->assertTrue($formField->getOptions()['required']);
    }

    public function testFormFieldNotRequired()
    {
        $field = $this->getField();
        $definition = $this->getDefinition(false);

        $formField = $field->getFormField($definition, 'global', 'Global');
        $this->assertInstanceOf(Field::class, $formField);
        $this->assertSame('value_global', $formField->getCode());
        $this->assertSame($this->getFieldClassName(), $formField->getType());
        $this->assertSame(2, $formField->getPosition());
        $this->assertSame('Global', $formField->getOptions()['label']);
        $this->assertFalse($formField->getOptions()['required']);
    }
}
