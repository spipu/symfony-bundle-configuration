<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;

class FieldBoolean extends FieldSelect
{
    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'boolean';
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     * @throws ConfigurationException
     */
    public function prepareValue(Definition $definition, $value)
    {
        $value = parent::prepareValue($definition, $value);

        if ($value !== null) {
            $value = (int) $value;
        }

        return $value;
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return int|mixed
     * @throws ConfigurationException
     */
    public function validateValue(Definition $definition, $value)
    {
        $value = parent::validateValue($definition, $value);

        if ($value !== null) {
            $value = (int) $value;
        }

        return $value;
    }
}
