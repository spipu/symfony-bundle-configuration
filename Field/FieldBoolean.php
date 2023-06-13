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
            $value = (int) $value;
        }

        return $value;
    }

    public function validateValue(Definition $definition, mixed $value): mixed
    {
        $value = parent::validateValue($definition, $value);

        if ($value !== null) {
            $value = (int) $value;
        }

        return $value;
    }
}
