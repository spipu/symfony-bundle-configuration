<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\UiBundle\Entity\Form\Field;
use Symfony\Component\Form\Extension\Core\Type;

class FieldColor extends AbstractField implements FieldInterface
{
    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'color';
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     * @throws ConfigurationException
     */
    public function validateValue(Definition $definition, $value)
    {
        $value = $this->validateValueType($definition, $value, null);

        if ($value !== null && !preg_match('/^#[0-9a-f]{6}$/', $value)) {
            throw new ConfigurationException(
                sprintf(
                    'Configuration "%s" must be a valid hexa rgb color',
                    $definition->getCode(),
                    $definition->getType()
                )
            );
        }

        return $value;
    }

    /**
     * @param Definition $definition
     * @return Field
     */
    public function getFormField(Definition $definition): Field
    {
        $field = new Field(
            'value',
            Type\ColorType::class,
            10,
            $this->getFieldBuilderOptions($definition)
        );

        return $field;
    }
}
