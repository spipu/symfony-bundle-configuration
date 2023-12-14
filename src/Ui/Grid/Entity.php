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

namespace Spipu\ConfigurationBundle\Ui\Grid;

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\UiBundle\Entity\EntityInterface;

class Entity extends Definition implements EntityInterface
{
    private mixed $value;

    public function getId(): ?int
    {
        return null;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
