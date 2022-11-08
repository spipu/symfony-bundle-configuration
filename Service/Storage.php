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
use Psr\Cache\InvalidArgumentException;
use Spipu\ConfigurationBundle\Entity\Configuration;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Exception\ConfigurationScopeException;
use Spipu\ConfigurationBundle\Repository\ConfigurationRepository;

class Storage
{
    public const CACHE_KEY = "spipu_configuration_cache";

    /**
     * @var Definitions
     */
    private $definitions;

    /**
     * @var mixed
     */
    private $values;

    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    /**
     * @var FieldList
     */
    private $fieldList;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var ScopeService
     */
    private $scopeService;

    /**
     * @var string[]
     */
    private $scoped;

    /**
     * @param Definitions $definitions
     * @param ConfigurationRepository $configurationRepository
     * @param FieldList $fieldList
     * @param EntityManagerInterface $entityManager
     * @param CacheItemPoolInterface $cache
     * @param ScopeService $scopeService
     */
    public function __construct(
        Definitions $definitions,
        ConfigurationRepository $configurationRepository,
        FieldList $fieldList,
        EntityManagerInterface $entityManager,
        CacheItemPoolInterface $cache,
        ScopeService $scopeService
    ) {
        $this->definitions = $definitions;
        $this->configurationRepository = $configurationRepository;
        $this->fieldList = $fieldList;
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->scopeService = $scopeService;
    }

    /**
     * @return array
     * @throws ConfigurationException
     */
    public function getAll(): array
    {
        $this->loadValues();

        return $this->values;
    }

    /**
     * @param string $key
     * @param string|null $scope
     * @return mixed
     * @throws ConfigurationException
     */
    public function get(string $key, ?string $scope)
    {
        $this->loadValues();

        $scopes = [];
        if (!in_array($scope, [null, '', 'global', 'default'])) {
            if (!in_array($key, $this->scoped)) {
                throw new ConfigurationException('This configuration key is not scoped');
            }

            if (!array_key_exists($scope, $this->values)) {
                throw new ConfigurationScopeException(sprintf('Unknown configuration scope [%s]', $scope));
            }

            $scopes[] = $scope;
        }
        $scopes[] = 'global';
        $scopes[] = 'default';

        foreach ($scopes as $scope) {
            if (array_key_exists($key, $this->values[$scope])) {
                return $this->values[$scope][$key];
            }
        }

        throw new ConfigurationException(sprintf('Unknown configuration key [%s]', $key));
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string|null $scope
     * @return void
     * @throws ConfigurationException
     */
    public function set(string $key, $value, ?string $scope): void
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

        $config = $this->configurationRepository->findOneBy(['code' => $key, 'scope' => $scope]);
        if (!$config) {
            $config = new Configuration();
            $config->setCode($key);
            $config->setScope($scope);

            $this->entityManager->persist($config);
        }
        $config->setValue($value);

        $this->entityManager->flush();

        $this->cleanValues();
    }

    /**
     * @param string $key
     * @param string|null $scope
     * @return void
     * @throws ConfigurationException
     */
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

        $config = $this->configurationRepository->findOneBy(['code' => $key, 'scope' => $scope]);
        if ($config) {
            $this->configurationRepository->remove($config);
        }

        $this->cleanValues();
    }
    /**
     * @return void
     * @throws ConfigurationException
     */
    private function loadValues(): void
    {
        if (is_array($this->values)) {
            return;
        }

        $this->loadValuesScoped();

        $cachedItem = $this->loadValuesCache();
        if ($cachedItem->isHit()) {
            $this->values = unserialize($cachedItem->get());
            return;
        }

        $this->loadValuesInit();
        $this->loadValuesDefault();
        $this->loadValuesDatabase();
        $this->loadValuesPrepare();

        $cachedItem->set(serialize($this->values));
        $cachedItem->expiresAfter(3600 * 24);
        $this->cache->save($cachedItem);
    }

    /**
     * @return void
     * @throws ConfigurationException
     */
    public function cleanValues(): void
    {
        $this->values = null;
        try {
            $this->cache->deleteItem(static::CACHE_KEY);
        } catch (InvalidArgumentException $e) {
            throw new ConfigurationException($e->getMessage());
        }
    }


    /**
     * @return CacheItemInterface
     * @throws ConfigurationException
     */
    public function loadValuesCache(): CacheItemInterface
    {
        try {
            return $this->cache->getItem(static::CACHE_KEY);
        } catch (InvalidArgumentException $e) {
            throw new ConfigurationException($e->getMessage());
        }
    }

    /**
     * @return void
     */
    private function loadValuesInit(): void
    {
        $this->values = [
            'default' => [],
            'global'  => [],
        ];

        if ($this->scopeService->hasScopes()) {
            foreach ($this->scopeService->getScopes() as $scope) {
                $this->values[$scope->getCode()] = [];
            }
        }
    }

    /**
     * Load the default values
     * @return void
     */
    private function loadValuesDefault(): void
    {
        foreach ($this->definitions->getAll() as $definition) {
            $this->values['default'][$definition->getCode()] = $definition->getDefault();
        }
    }

    /**
     * Load the database values
     * @return void
     */
    private function loadValuesDatabase(): void
    {
        $rows = $this->configurationRepository->findAll();

        foreach ($rows as $row) {
            $code = $row->getCode();
            $scopeCode = $row->getScope();
            if ($scopeCode === '' || $scopeCode === null) {
                $scopeCode = 'global';
            }

            if (!array_key_exists($scopeCode, $this->values) || !array_key_exists($code, $this->values['default'])) {
                $this->configurationRepository->remove($row);
                continue;
            }

            $this->values[$scopeCode][$code] = $row->getValue();
        }
    }

    /**
     * Prepare the values
     * @return void
     * @throws ConfigurationException
     */
    private function loadValuesPrepare(): void
    {
        foreach ($this->values as $scopeCode => $values) {
            foreach ($values as $code => $value) {
                $this->values[$scopeCode][$code] = $this->fieldList->prepareValue(
                    $this->definitions->get($code),
                    $value
                );
            }
        }
    }

    /**
     * @param string|null $scope
     * @return string
     * @throws ConfigurationScopeException
     */
    private function validateScope(?string $scope): string
    {
        if ($scope === '' || $scope === null) {
            return 'global';
        }

        return $this->scopeService->getScope($scope)->getCode();
    }

    /**
     * @return void
     */
    private function loadValuesScoped(): void
    {
        $this->scoped = [];

        foreach ($this->definitions->getAll() as $definition) {
            if ($definition->isScoped()) {
                $this->scoped[] = $definition->getCode();
            }
        }
    }
}
