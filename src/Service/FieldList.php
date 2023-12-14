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
    private array $fields = [];

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

    public function prepareValue(Definition $definition, mixed $value): mixed
    {
        return $this->getField($definition)->prepareValue($definition, $value);
    }

    public function validateValue(Definition $definition, mixed $value): mixed
    {
        return $this->getField($definition)->validateValue($definition, $value);
    }

    public function getField(Definition $definition): FieldInterface
    {
        return $this->fields[$definition->getType()];
    }

    public function getFormField(Definition $definition, string $scopeCode, string $scopeName): Field
    {
        return $this->getField($definition)->getFormField($definition, $scopeCode, $scopeName);
    }
}
