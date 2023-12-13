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

class FieldUrl extends AbstractField implements FieldInterface
{
    public function getCode(): string
    {
        return 'url';
    }

    public function validateValue(Definition $definition, mixed $value): mixed
    {
        return $this->validateValueType($definition, $value, FILTER_VALIDATE_URL);
    }

    public function getFormField(Definition $definition, string $scopeCode, string $scopeName): Field
    {
        return new Field(
            $this->buildFormFieldCode($scopeCode),
            Type\UrlType::class,
            10,
            $this->getFieldBuilderOptions($definition, $scopeCode, $scopeName)
        );
    }
}
