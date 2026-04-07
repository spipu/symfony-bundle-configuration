<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldString;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(FieldString::class)]
class FieldStringTest extends AbstractFieldTestCase
{
    protected function getCode(): string
    {
        return 'string';
    }

    protected function getField(): object
    {
        return new FieldString();
    }

    protected function getDefinition(bool $required, bool $scoped = false): Definition
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, 'test', 'help', null);
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
        return \Symfony\Component\Form\Extension\Core\Type\TextType::class;
    }

    public function testFormFieldOther(): void
    {
        $field = $this->getField();
        $definition = $this->getDefinition(true);

        $formField = $field->getFormField($definition, 'global', 'Global');
        $this->assertSame('Unit: test | help', $formField->getOptions()['help']);

        $formField = $field->getFormField($definition, 'default', 'Default');
        $this->assertSame(10, $formField->getPosition());

        $formField = $field->getFormField($definition, 'global', 'Global');
        $this->assertSame(10, $formField->getPosition());
    }
}
