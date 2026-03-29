<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Field\FieldSelect;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FieldSelectTest extends AbstractFieldTest
{
    protected function getCode(): string
    {
        return 'select';
    }

    protected function getField(): object
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['good_options', 1, SpipuUiMock::getOptionStringMock()],
                        ['bad_options', 1, new \stdClass()],
                    ]
                )
            );

        return new FieldSelect($container);
    }

    protected function getDefinition(bool $required, bool $scoped = false): Definition
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, 'good_options', null, null, null);
    }

    protected function getGoodValue(): mixed
    {
        return 'yes';
    }

    protected function getBadValue(): mixed
    {
        return 'bad';
    }

    protected function getEmptyValue(): mixed
    {
        return '';
    }

    protected function getFieldClassName(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class;
    }

    public function testBadOptions(): void
    {
        $field = $this->getField();
        $definition = new Definition('mock.test', $this->getCode(), false, false, null, 'bad_options', null, null, null);

        $this->expectException(ConfigurationException::class);
        $field->validateValue($definition, 'yes');
    }
}
