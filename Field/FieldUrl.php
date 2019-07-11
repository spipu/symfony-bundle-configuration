<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\UiBundle\Entity\Form\Field;
use Symfony\Component\Form\Extension\Core\Type;

class FieldUrl extends AbstractField implements FieldInterface
{
    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'url';
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     * @throws ConfigurationException
     */
    public function validateValue(Definition $definition, $value)
    {
        return $this->validateValueType($definition, $value, FILTER_VALIDATE_URL);
    }

    /**
     * @param Definition $definition
     * @return Field
     */
    public function getFormField(Definition $definition): Field
    {
        $field = new Field(
            'value',
            Type\UrlType::class,
            10,
            $this->getFieldBuilderOptions($definition)
        );

        return $field;
    }
}
