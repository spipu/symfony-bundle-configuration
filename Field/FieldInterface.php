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

interface FieldInterface
{
    public function getCode(): string;

    public function prepareValue(Definition $definition, mixed $value): mixed;

    public function validateValue(Definition $definition, mixed $value): mixed;

    public function getFormField(Definition $definition, string $scopeCode, string $scopeName): Field;
}
