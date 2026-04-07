<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldEncrypted;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(FieldEncrypted::class)]
class FieldEncryptedTest extends AbstractFieldTestCase
{
    protected function getCode(): string
    {
        return 'encrypted';
    }

    protected function getField(): object
    {
        return new FieldEncrypted();
    }

    protected function getDefinition(bool $required, bool $scoped = false): Definition
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, 'test', null, null);
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
}
