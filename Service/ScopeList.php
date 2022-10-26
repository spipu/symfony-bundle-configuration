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

use Spipu\ConfigurationBundle\Entity\Scope;

class ScopeList implements ScopeListInterface
{
    /**
     * @return Scope[]
     */
    public function getAll(): array
    {
        return [];
    }
}
