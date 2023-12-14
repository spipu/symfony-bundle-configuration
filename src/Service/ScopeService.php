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
use Spipu\ConfigurationBundle\Exception\ConfigurationScopeException;

class ScopeService
{
    private ScopeListInterface $scopeList;

    /**
     * @var Scope[]
     */
    private ?array $scopes = null;

    public function __construct(
        ScopeListInterface $scopeList
    ) {
        $this->scopeList = $scopeList;
    }

    private function loadScopes(): void
    {
        if ($this->scopes !== null) {
            return;
        }

        $this->scopes = [];
        foreach ($this->scopeList->getAll() as $scope) {
            $this->addScope($scope);
        }
    }

    /**
     * @param Scope $scope
     * @return void
     */
    private function addScope(Scope $scope): void
    {
        if (array_key_exists($scope->getCode(), $this->scopes)) {
            throw new ConfigurationScopeException('configuration scope code already existing');
        }

        $this->scopes[$scope->getCode()] = $scope;
    }

    /**
     * @return Scope[]
     */
    public function getScopes(): array
    {
        $this->loadScopes();

        return $this->scopes;
    }

    /**
     * @return Scope[]
     */
    public function getSortedScopes(): array
    {
        $scopes = $this->getScopes();

        uasort(
            $scopes,
            function (Scope $scopeA, Scope $scopeB) {
                return $scopeA->getName() <=> $scopeB->getName();
            }
        );

        return $scopes;
    }

    /**
     * @return bool
     */
    public function hasScopes(): bool
    {
        $this->loadScopes();

        return (count($this->scopes) > 0);
    }

    /**
     * @param string|null $code
     * @return Scope|null
     * @throws ConfigurationScopeException
     */
    public function getScope(?string $code): ?Scope
    {
        $this->loadScopes();

        if ($code === null || $code === '') {
            return null;
        }

        if (array_key_exists($code, $this->scopes)) {
            return $this->scopes[$code];
        }

        throw new ConfigurationScopeException('Unknown configuration scope code');
    }
}
