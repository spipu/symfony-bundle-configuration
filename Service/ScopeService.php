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
    /**
     * @var ScopeListInterface
     */
    private $scopeList;

    /**
     * @var Scope[]
     */
    private $scopes;

    /**
     * @param ScopeListInterface $scopeList
     */
    public function __construct(
        ScopeListInterface $scopeList
    ) {
        $this->scopeList = $scopeList;
    }

    /**
     * @return void
     */
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

        if ($code === null) {
            return null;
        }

        if (array_key_exists($code, $this->scopes)) {
            return $this->scopes[$code];
        }

        throw new ConfigurationScopeException('Unknown configuration scope code');
    }
}
