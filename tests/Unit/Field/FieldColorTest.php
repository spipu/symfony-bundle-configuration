<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldColor;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(FieldColor::class)]
class FieldColorTest extends AbstractFieldTestCase
{
    protected function getCode(): string
    {
        return 'color';
    }

    protected function getField(): object
    {
        return new FieldColor();
    }

    protected function getDefinition(bool $required, bool $scoped = false): Definition
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, null, null, null);
    }

    protected function getGoodValue(): mixed
    {
        return '#00aaCC';
    }

    protected function getBadValue(): mixed
    {
        return 'ayh56';
    }

    protected function getEmptyValue(): mixed
    {
        return '';
    }

    protected function getFieldClassName(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\ColorType::class;
    }
}
