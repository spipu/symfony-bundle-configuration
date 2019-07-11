<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;

abstract class AbstractField implements FieldInterface
{
    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     */
    public function prepareValue(Definition $definition, $value)
    {
        if (!$definition->isRequired() && ($value === null || $value === '')) {
            return null;
        }

        return (string) $value;
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed|null
     * @throws ConfigurationException
     */
    protected function isRequired(Definition $definition, $value)
    {
        if ($value !== null) {
            $value = (string) $value;
        }
        if ($value === '') {
            $value = null;
        }

        if ($definition->isRequired() && $value === null) {
            throw new ConfigurationException(
                sprintf(
                    'Configuration "%s" is required',
                    $definition->getCode()
                )
            );
        }

        return $value;
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @param int|null $validator
     * @return string|null
     * @throws ConfigurationException
     */
    protected function validateValueType(Definition $definition, $value, ?int $validator)
    {
        $value = $this->isRequired($definition, $value);

        if ($validator && $value !== null && !filter_var($value, $validator)) {
            throw new ConfigurationException(
                sprintf(
                    'Configuration "%s" must be a valid %s',
                    $definition->getCode(),
                    $definition->getType()
                )
            );
        }

        return $value;
    }

    /**
     * @param Definition $definition
     * @return array
     */
    protected function getFieldBuilderOptions(Definition $definition): array
    {
        $options = [
            'label'    => 'spipu.configuration.field.value',
            'required' => $definition->isRequired(),
        ];

        if ($definition->getUnit()) {
            $options['help'] = 'Unit: '.$definition->getUnit();
        }

        return $options;
    }
}
