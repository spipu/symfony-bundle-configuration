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

namespace Spipu\ConfigurationBundle\Form\Options;

use Spipu\ConfigurationBundle\Service\ScopeListInterface;
use Spipu\UiBundle\Form\Options\AbstractOptions;

class ScopeOptions extends AbstractOptions
{
    /**
     * @var ScopeListInterface
     */
    private ScopeListInterface $scopeList;

    /**
     * @param ScopeListInterface $scopeList
     */
    public function __construct(ScopeListInterface $scopeList)
    {
        $this->scopeList = $scopeList;
    }

    /**
     * Build the list of the available options
     * @return array
     */
    protected function buildOptions(): array
    {
        $list = [];
        foreach ($this->scopeList->getAll() as $scope) {
            $list[$scope->getCode()] = $scope->getName();
        }

        return $list;
    }
}
