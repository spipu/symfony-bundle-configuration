<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Field\FieldSelect;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FieldSelectTest extends AbstractFieldTest
{
    protected function getCode()
    {
        return 'select';
    }

    protected function getField()
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

    protected function getDefinition(bool $required, bool $perScope = false)
    {
        return new Definition('mock.test', $this->getCode(), $required, $perScope, null, 'good_options', null, null, null);
    }

    protected function getGoodValue()
    {
        return 'yes';
    }

    protected function getBadValue()
    {
        return 'bad';
    }

    protected function getEmptyValue()
    {
        return '';
    }

    protected function getFieldClassName()
    {
        return \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class;
    }

    public function testBadOptions()
    {
        $field = $this->getField();
        $definition = new Definition('mock.test', $this->getCode(), false, false, null, 'bad_options', null, null, null);

        $this->expectException(ConfigurationException::class);
        $field->validateValue($definition, 'yes');
    }
}
