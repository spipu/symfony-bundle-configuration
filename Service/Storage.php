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
use Psr\Cache\CacheItemPoolInterface;
use Spipu\ConfigurationBundle\Entity\Configuration;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
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
     * @param Definitions $definitions
     * @param ConfigurationRepository $configurationRepository
     * @param FieldList $fieldList
     * @param EntityManagerInterface $entityManager
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(
        Definitions $definitions,
        ConfigurationRepository $configurationRepository,
        FieldList $fieldList,
        EntityManagerInterface $entityManager,
        CacheItemPoolInterface $cache
    ) {
        $this->definitions = $definitions;
        $this->configurationRepository = $configurationRepository;
        $this->fieldList = $fieldList;
        $this->entityManager = $entityManager;
        $this->cache = $cache;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $this->loadValues();

        return $this->values;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws ConfigurationException
     */
    public function get(string $key)
    {
        $this->loadValues();

        if (!array_key_exists($key, $this->values)) {
            throw new ConfigurationException(sprintf('Unknown configuration key [%s]', $key));
        }

        return $this->values[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws ConfigurationException
     */
    public function set(string $key, $value): void
    {
        $definition = $this->definitions->get($key);

        $value = $this->fieldList->validateValue($definition, $value);
        if ($value !== null) {
            $value = (string) $value;
        }

        $config = $this->configurationRepository->findOneBy(['code' => $key, 'scope' => null]);
        if (!$config) {
            $config = new Configuration();
            $config->setCode($key);
            $config->setScope(null);

            $this->entityManager->persist($config);
        }
        $config->setValue($value);

        $this->entityManager->flush();

        $this->cleanValues();
    }

    /**
     * @return void
     */
    private function loadValues(): void
    {
        if (is_array($this->values)) {
            return;
        }

        $cachedItem = $this->cache->getItem(static::CACHE_KEY);

        if ($cachedItem->isHit()) {
            $this->values = unserialize($cachedItem->get());
            return;
        }

        $this->loadDefaultValues();
        $this->loadDatabaseValues();
        $this->prepareValues();

        $cachedItem->set(serialize($this->values));
        $cachedItem->expiresAfter(3600 * 24);
        $this->cache->save($cachedItem);
    }

    /**
     * @return void
     */
    public function cleanValues(): void
    {
        $this->values = null;
        $this->cache->deleteItem(static::CACHE_KEY);
    }

    /**
     * Load the default values
     * @return void
     */
    private function loadDefaultValues(): void
    {
        $this->values = [];
        foreach ($this->definitions->getAll() as $definition) {
            $this->values[$definition->getCode()] = $definition->getDefault();
        }
    }

    /**
     * Load the database values
     * @return void
     */
    private function loadDatabaseValues(): void
    {
        $rows = $this->configurationRepository->findAll();

        foreach ($rows as $row) {
            if (array_key_exists($row->getCode(), $this->values)) {
                $this->values[$row->getCode()] = $row->getValue();
            }
        }
    }

    /**
     * Prepare the values
     * @return void
     */
    private function prepareValues(): void
    {
        $definitions = $this->definitions->getAll();

        foreach ($definitions as $definition) {
            $this->values[$definition->getCode()] = $this->fieldList->prepareValue(
                $definition,
                $this->values[$definition->getCode()]
            );
        }
    }
}
