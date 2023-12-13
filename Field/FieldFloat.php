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
use Spipu\UiBundle\Entity\Form\Field;
use Symfony\Component\Form\Extension\Core\Type;

class FieldFloat extends AbstractField implements FieldInterface
{
    public function getCode(): string
    {
        return 'float';
    }

    public function prepareValue(Definition $definition, mixed $value): mixed
    {
        $value = parent::prepareValue($definition, $value);

        if ($value !== null) {
            $value = (float) $value;
        }

        return $value;
    }

    public function validateValue(Definition $definition, mixed $value): mixed
    {
        // Issue on PHP filter_var when testing 0 as float.
        if ($value === 0 | preg_match('/^0+$/', (string) $value)) {
            return 0.;
        }

        $value = $this->validateValueType($definition, $value, FILTER_VALIDATE_FLOAT);

        if ($value !== null) {
            $value = (float) $value;
        }

        return $value;
    }

    public function getFormField(Definition $definition, string $scopeCode, string $scopeName): Field
    {
        return new Field(
            $this->buildFormFieldCode($scopeCode),
            Type\NumberType::class,
            10,
            $this->getFieldBuilderOptions($definition, $scopeCode, $scopeName)
        );
    }
}
