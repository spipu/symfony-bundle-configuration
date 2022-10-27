<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Field\FieldBoolean;
use Spipu\UiBundle\Form\Options\BooleanStatus;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FieldBooleanTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'boolean';
    }

    protected function getField()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap([['good_options', 1, new BooleanStatus()]])
            );

        return new FieldBoolean($container);
    }

    protected function getDefinition(bool $required, bool $scoped = false)
    {
        return new Definition('mock.test', $this->getCode(), $required, $scoped, null, 'good_options', null, null, null);
    }

    protected function getGoodValue()
    {
        return 1;
    }

    protected function getBadValue()
    {
        return 2;
    }

    protected function getEmptyValue()
    {
        return 0;
    }

    protected function getFieldClassName()
    {
        return \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class;
    }
}
