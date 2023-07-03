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

class FieldBoolean extends FieldSelect
{
    public function getCode(): string
    {
        return 'boolean';
    }

    public function prepareValue(Definition $definition, mixed $value): mixed
    {
        $value = parent::prepareValue($definition, $value);

        if ($value !== null) {
            $value = (((int) $value) > 0 ? 1 : 0);
        }

        return $value;
    }

    public function validateValue(Definition $definition, mixed $value): mixed
    {
        $value = parent::validateValue($definition, $value);

        if ($value !== null) {
            $value = (((int) $value) > 0 ? 1 : 0);
        }

        return $value;
    }
}
