<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldUrl;

class FieldUrlTest extends AbstractFieldTest
{
    protected function getCode(): string
    {
        return 'url';
    }

    protected function getField(): object
    {
        return new FieldUrl();
    }

    protected function getDefinition(bool $required, bool $scoped = false): Definition
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, null, null, null, null);
    }

    protected function getGoodValue(): mixed
    {
        return 'http://test.fr/';
    }

    protected function getBadValue(): mixed
    {
        return 'bad_url !!!';
    }

    protected function getEmptyValue(): mixed
    {
        return '';
    }

    protected function getFieldClassName(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\UrlType::class;
    }
}
