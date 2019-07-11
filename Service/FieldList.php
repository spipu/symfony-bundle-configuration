<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Service;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Field\FieldInterface;
use Spipu\UiBundle\Entity\Form\Field;

class FieldList implements FieldInterface
{
    /**
     * @var FieldInterface[]
     */
    private $fields = [];

    /**
     * ActionList constructor.
     * @param iterable $fields
     * @throws ConfigurationException
     */
    public function __construct(
        iterable $fields
    ) {
        foreach ($fields as $field) {
            if (!($field instanceof FieldInterface)) {
                throw new ConfigurationException('Only FieldInterface is allowed');
            }
            $this->fields[$field->getCode()] = $field;
        }
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'list';
    }

    /**
     * @return FieldInterface[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     */
    public function prepareValue(Definition $definition, $value)
    {
        return $this->getField($definition)->prepareValue($definition, $value);
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     * @throws ConfigurationException
     */
    public function validateValue(Definition $definition, $value)
    {
        return $this->getField($definition)->validateValue($definition, $value);
    }

    /**
     * @param Definition $definition
     * @return FieldInterface
     */
    public function getField(Definition $definition): FieldInterface
    {
        return $this->fields[$definition->getType()];
    }

    /**
     * @param Definition $definition
     * @return Field
     */
    public function getFormField(Definition $definition): Field
    {
        return $this->getField($definition)->getFormField($definition);
    }
}
