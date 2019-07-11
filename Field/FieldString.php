<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\UiBundle\Entity\Form\Field;
use Symfony\Component\Form\Extension\Core\Type;

class FieldString extends AbstractField implements FieldInterface
{
    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'string';
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     * @throws ConfigurationException
     */
    public function validateValue(Definition $definition, $value)
    {
        return $this->validateValueType($definition, $value, null);
    }

    /**
     * @param Definition $definition
     * @return Field
     */
    public function getFormField(Definition $definition): Field
    {
        $field = new Field(
            'value',
            Type\TextType::class,
            10,
            $this->getFieldBuilderOptions($definition)
        );

        return $field;
    }
}
