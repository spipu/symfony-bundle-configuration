<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Field;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\UiBundle\Entity\Form\Field;
use Symfony\Component\Form\Extension\Core\Type;

class FieldFloat extends AbstractField implements FieldInterface
{
    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'float';
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     */
    public function prepareValue(Definition $definition, $value)
    {
        $value = parent::prepareValue($definition, $value);

        if ($value !== null) {
            $value = (float) $value;
        }

        return $value;
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return mixed
     * @throws ConfigurationException
     */
    public function validateValue(Definition $definition, $value)
    {
        // Issue on PHP filter_var when testing 0 as float.
        if ($value === 0 | preg_match('/^[0]+$/', (string) $value)) {
            return 0.;
        }

        $value = $this->validateValueType($definition, $value, FILTER_VALIDATE_FLOAT);

        if ($value !== null) {
            $value = (float) $value;
        }

        return $value;
    }

    /**
     * @param Definition $definition
     * @return Field
     */
    public function getFormField(Definition $definition): Field
    {
        return new Field(
            'value',
            Type\NumberType::class,
            10,
            $this->getFieldBuilderOptions($definition)
        );
    }
}
