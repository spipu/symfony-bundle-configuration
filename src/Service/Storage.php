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

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Spipu\ConfigurationBundle\Entity\Configuration;
use Spipu\ConfigurationBundle\Event\ConfigurationEvent;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Exception\ConfigurationScopeException;
use Spipu\ConfigurationBundle\Repository\ConfigurationRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @SuppressWarnings(PMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class Storage
{
    public const CACHE_KEY = "spipu_configuration_cache";

    private Definitions $definitions;
    private ConfigurationRepository $configurationRepository;
    private FieldList $fieldList;
    private EntityManagerInterface $entityManager;
    private CacheItemPoolInterface $cacheService;
    private ScopeService $scopeService;
    private EventDispatcherInterface $eventDispatcher;
    private ?array $cacheValues = null;

    public function __construct(
        Definitions $definitions,
        ConfigurationRepository $configurationRepository,
        FieldList $fieldList,
        EntityManagerInterface $entityManager,
        CacheItemPoolInterface $cacheService,
        ScopeService $scopeService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->definitions = $definitions;
        $this->configurationRepository = $configurationRepository;
        $this->fieldList = $fieldList;
        $this->entityManager = $entityManager;
        $this->cacheService = $cacheService;
        $this->scopeService = $scopeService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getAll(): array
    {
        $this->loadValues();

        return $this->cacheValues['values'];
    }

    public function get(string $key, ?string $scope): mixed
    {
        $this->loadValues();

        $scopes = [];
        if (!in_array($scope, [null, '', 'global', 'default'], true)) {
            if (!in_array($key, $this->cacheValues['scoped'], true)) {
                throw new ConfigurationException('This configuration key is not scoped');
            }

            if (!array_key_exists($scope, $this->cacheValues['values'])) {
                throw new ConfigurationScopeException(sprintf('Unknown configuration scope [%s]', $scope));
            }

            $scopes[] = $scope;
        }

        if ($scope !== 'default') {
            $scopes[] = 'global';
        }
        $scopes[] = 'default';

        foreach ($scopes as $scope) {
            if (array_key_exists($key, $this->cacheValues['values'][$scope])) {
                return $this->cacheValues['values'][$scope][$key];
            }
        }

        throw new ConfigurationException(sprintf('Unknown configuration key [%s]', $key));
    }

    public function getScopeValue(string $key, string $scope): mixed
    {
        $this->loadValues();

        if (!array_key_exists($scope, $this->cacheValues['values'])) {
            throw new ConfigurationScopeException(sprintf('Unknown configuration scope [%s]', $scope));
        }

        if (!array_key_exists($key, $this->cacheValues['values']['default'])) {
            throw new ConfigurationException(sprintf('Unknown configuration key [%s]', $key));
        }

        if (!array_key_exists($key, $this->cacheValues['values'][$scope])) {
            throw new ConfigurationException(sprintf('no value for key [%s] on scope [%s]', $key, $scope));
        }

        return $this->cacheValues['values'][$scope][$key];
    }

    public function set(string $key, mixed $value, ?string $scope): void
    {
        $scope = $this->validateScope($scope);
        if ($scope === 'global') {
            $scope = null;
        }

        $definition = $this->definitions->get($key);

        if ($scope !== null && !$definition->isScoped()) {
            throw new ConfigurationException('This configuration key is not scoped');
        }

        $value = $this->fieldList->validateValue($definition, $value);
        if ($value !== null) {
            $value = (string) $value;
        }

        $config = $this->configurationRepository->loadConfig($key, $scope);
        if (!$config) {
            $config = new Configuration();
            $config->setCode($key);
            $config->setScope($scope);

            $this->entityManager->persist($config);
        }
        $config->setValue($value);

        $this->entityManager->flush();

        $this->cleanValues();

        $event = new ConfigurationEvent($definition, $scope);
        $this->eventDispatcher->dispatch($event, $event->getGlobalEventCode());
        $this->eventDispatcher->dispatch($event, $event->getSpecificEventCode());
    }

    public function delete(string $key, ?string $scope): void
    {
        $scope = $this->validateScope($scope);
        if ($scope === 'global') {
            $scope = null;
        }

        $definition = $this->definitions->get($key);

        if ($scope !== null && !$definition->isScoped()) {
            throw new ConfigurationException('This configuration key is not scoped');
        }

        $config = $this->configurationRepository->loadConfig($key, $scope);
        if ($config) {
            $this->configurationRepository->remove($config);
        }

        $this->cleanValues();

        $event = new ConfigurationEvent($definition, $scope);
        $this->eventDispatcher->dispatch($event, $event->getGlobalEventCode());
        $this->eventDispatcher->dispatch($event, $event->getSpecificEventCode());
    }

    private function loadValues(): void
    {
        if (is_array($this->cacheValues)) {
            return;
        }

        $cachedItem = $this->loadValuesCache();
        if ($cachedItem->isHit()) {
            $cache = unserialize($cachedItem->get());
            if (is_array($cache)) {
                $this->cacheValues = $cache;
            }
            return;
        }

        $this->loadValuesInit();
        $this->loadValuesScoped();
        $this->loadValuesDefault();
        $this->loadValuesDatabase();
        $this->loadValuesPrepare();

        $cachedItem->set(serialize($this->cacheValues));
        $cachedItem->expiresAfter(3600 * 24);
        $this->cacheService->save($cachedItem);
    }

    public function cleanValues(): void
    {
        $this->cacheValues = null;
        $this->cacheService->deleteItem(static::CACHE_KEY);
    }

    private function loadValuesCache(): CacheItemInterface
    {
        return $this->cacheService->getItem(static::CACHE_KEY);
    }

    private function loadValuesInit(): void
    {
        $this->cacheValues = [
            'scoped' => [],
            'values' => [
                'default' => [],
                'global'  => [],
            ],
        ];

        if ($this->scopeService->hasScopes()) {
            foreach ($this->scopeService->getScopes() as $scope) {
                $this->cacheValues['values'][$scope->getCode()] = [];
            }
        }
    }

    private function loadValuesScoped(): void
    {
        foreach ($this->definitions->getAll() as $definition) {
            if ($definition->isScoped()) {
                $this->cacheValues['scoped'][] = $definition->getCode();
            }
        }
    }

    private function loadValuesDefault(): void
    {
        foreach ($this->definitions->getAll() as $definition) {
            $this->cacheValues['values']['default'][$definition->getCode()] = $definition->getDefault();
        }
    }

    private function loadValuesDatabase(): void
    {
        $rows = $this->configurationRepository->findAll();

        foreach ($rows as $row) {
            $code = $row->getCode();
            $scopeCode = $row->getScope();
            if ($scopeCode === '' || $scopeCode === null) {
                $scopeCode = 'global';
            }

            if (
                !array_key_exists($scopeCode, $this->cacheValues['values'])
                || !array_key_exists($code, $this->cacheValues['values']['default'])
            ) {
                $this->configurationRepository->remove($row);
                continue;
            }

            $this->cacheValues['values'][$scopeCode][$code] = $row->getValue();
        }
    }

    private function loadValuesPrepare(): void
    {
        foreach ($this->cacheValues['values'] as $scopeCode => $values) {
            foreach ($values as $code => $value) {
                $this->cacheValues['values'][$scopeCode][$code] = $this->fieldList->prepareValue(
                    $this->definitions->get($code),
                    $value
                );
            }
        }
    }

    public function validateScope(?string $scope): string
    {
        if ($scope === '' || $scope === null) {
            return 'global';
        }

        return $this->scopeService->getScope($scope)->getCode();
    }
}
