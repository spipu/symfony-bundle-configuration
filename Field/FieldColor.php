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
use Spipu\UiBundle\Exception\FormException;
use Symfony\Component\Form\Extension\Core\Type;

class FieldColor extends AbstractField implements FieldInterface
{
    public function getCode(): string
    {
        return 'color';
    }

    public function validateValue(Definition $definition, mixed $value): mixed
    {
        $value = $this->validateValueType($definition, $value, null);

        if ($value !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            throw new ConfigurationException(
                sprintf(
                    'Configuration "%s" must be a valid hexadecimal rgb color',
                    $definition->getCode()
                )
            );
        }

        return $value;
    }

    public function getFormField(Definition $definition, string $scopeCode, string $scopeName): Field
    {
        return new Field(
            $this->buildFormFieldCode($scopeCode),
            Type\ColorType::class,
            $this->buildFormFieldPosition($scopeCode),
            $this->getFieldBuilderOptions($definition, $scopeCode, $scopeName)
        );
    }
}
