<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Entity\Configuration;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Field\FieldInterface;
use Spipu\ConfigurationBundle\Repository\ConfigurationRepository;
use Spipu\CoreBundle\Service\EncoderFactory;
use Spipu\CoreBundle\Service\EncryptorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Class Manager
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class Manager
{
    const CACHE_KEY = "spipu_configuration_cache";

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    /**
     * @var EncoderFactory
     */
    private $encoderFactory;

    /**
     * @var PasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var FieldList
     */
    private $fieldList;

    /**
     * @var Definition[]
     */
    private $definitions;

    /**
     * @var mixed
     */
    private $values;

    /**
     * @var string
     */
    private $filePath;

    /**
     * Configuration constructor.
     * @param ContainerInterface $container
     * @param EntityManagerInterface $entityManager
     * @param CacheItemPoolInterface $cache
     * @param ConfigurationRepository $configurationRepository
     * @param EncoderFactory $encoderFactory
     * @param EncryptorInterface $encryptor
     * @param FieldList $fieldList
     * @throws ConfigurationException
     */
    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $entityManager,
        CacheItemPoolInterface $cache,
        ConfigurationRepository $configurationRepository,
        EncoderFactory $encoderFactory,
        EncryptorInterface $encryptor,
        FieldList $fieldList
    ) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->configurationRepository = $configurationRepository;
        $this->encoderFactory = $encoderFactory;
        $this->encryptor = $encryptor;
        $this->fieldList = $fieldList;

        $this->loadDefinitions();
    }

    /**
     * Load the definitions
     *
     * @return bool
     * @throws ConfigurationException
     */
    private function loadDefinitions(): bool
    {
        $this->definitions = [];
        $configurations = $this->container->getParameter('spipu_configuration');
        foreach ($configurations as $configuration) {
            $definition = new Definition(
                $configuration['code'],
                $configuration['type'],
                $configuration['required'],
                $configuration['default'],
                $configuration['options'],
                $configuration['unit'],
                $configuration['file_type']
            );

            $this->definitions[$definition->getCode()] = $definition;
        }

        return true;
    }

    /**
     * Get the configuration definitions
     * @return Definition[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * Get the configuration definition of a specific key
     * @param string $key
     * @return Definition
     * @throws ConfigurationException
     */
    public function getDefinition(string $key): Definition
    {
        if (!array_key_exists($key, $this->definitions)) {
            throw new ConfigurationException(sprintf('Unknown configuration key [%s]', $key));
        }

        return $this->definitions[$key];
    }

    /**
     * @param string $key
     * @return FieldInterface
     * @throws ConfigurationException
     */
    public function getField(string $key): FieldInterface
    {
        return $this->fieldList->getField($this->getDefinition($key));
    }

    /**
     * Get all the configuration values
     * @return array
     */
    public function getAll(): array
    {
        $this->loadValues();

        return $this->values;
    }

    /**
     * Get a configuration value
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
        $definition = $this->getDefinition($key);

        $value = $this->fieldList->validateValue($definition, $value);
        if ($value !== null) {
            $value = (string) $value;
        }

        $config = $this->configurationRepository->findOneBy(['code' => $key]);
        if (!$config) {
            $config = new Configuration();
            $config->setCode($key);
        }
        $config->setValue($value);

        $this->entityManager->persist($config);
        $this->entityManager->flush();

        $this->cleanValues();
    }

    /**
     * @param string $key
     * @param string $raw
     * @return bool
     * @throws ConfigurationException
     */
    public function isPasswordValid(string $key, string $raw): bool
    {
        $encoded = $this->get($key);

        return $this->getEncoder()->isPasswordValid($encoded, $raw, null);
    }

    /**
     * @param string $key
     * @param ?string $value
     * @return void
     * @throws ConfigurationException
     */
    public function setPassword(string $key, ?string $value): void
    {
        if ($value !== null) {
            $value = $this->getEncoder()->encodePassword($value, null);
        }

        $this->set($key, $value);
    }

    /**
     * @return PasswordEncoderInterface
     */
    private function getEncoder(): PasswordEncoderInterface
    {
        if (!$this->encoder) {
            $this->encoder = $this->encoderFactory->create();
        }

        return $this->encoder;
    }

    /**
     * @param string $key
     * @return string|null
     * @throws ConfigurationException
     */
    public function getEncrypted(string $key): ?string
    {
        $value = $this->get($key);
        if ($value === null) {
            return null;
        }

        return $this->encryptor->decrypt((string) $value);
    }

    /**
     * @param string $key
     * @param ?string $value
     * @return void
     * @throws ConfigurationException
     */
    public function setEncrypted(string $key, ?string $value): void
    {
        if ($value === null) {
            $this->set($key, null);
            return;
        }

        $this->set($key, $this->encryptor->encrypt((string) $value));
    }


    /**
     * @param string $key
     * @param UploadedFile $file
     * @return void
     * @throws ConfigurationException
     */
    public function setFile(string $key, UploadedFile $file): void
    {
        if (!$this->container->getParameter('spipu.configuration.file.allow')) {
            throw new ConfigurationException('File are not allowed. Look at spipu.configuration.file.allow parameter');
        }

        $definition = $this->getDefinition($key);
        if ($definition->getType() !== 'file') {
            throw new ConfigurationException('This configuration is not a file!');
        }

        $fileTypes = $definition->getFileTypes();
        $guessExtension = $file->guessExtension();
        if (!empty($fileTypes) && ($guessExtension === null || !in_array(strtolower($guessExtension), $fileTypes))) {
            throw new ConfigurationException('File extension not allowed: '.$guessExtension);
        }

        $path = $this->getFilePath();
        if (!is_dir($path)  || !is_writable($path)) {
            throw new ConfigurationException('The file path does not exist or is not writable: '.$path);
        }

        $fileName  = md5($definition->getCode()) . '.' . $file->guessExtension();

        $file->move($path, $fileName);

        $this->set($key, $fileName);
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        if ($this->filePath === null) {
            $this->filePath = $this->container->getParameter('kernel.project_dir')
                . DIRECTORY_SEPARATOR
                . $this->container->getParameter('spipu.configuration.file.path');

            $this->filePath = rtrim($this->filePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getFileUrl(): string
    {
        return $this->container->getParameter('spipu.configuration.file.url');
    }

    /**
     * Clean the values to force reload
     * @return void
     */
    private function cleanValues(): void
    {
        $this->values = null;

        $this->cache->deleteItem(static::CACHE_KEY);
    }

    /**
     * Load the values
     * @return bool
     */
    private function loadValues(): bool
    {
        if (is_array($this->values)) {
            return false;
        }

        $cachedItem = $this->cache->getItem(static::CACHE_KEY);

        if ($cachedItem->isHit()) {
            $this->values = unserialize($cachedItem->get());
            return false;
        }

        $this->loadDefaultValues();
        $this->loadDatabaseValues();
        $this->prepareValues();

        $cachedItem->set(serialize($this->values));
        $cachedItem->expiresAfter(3600 * 24);
        $this->cache->save($cachedItem);

        return true;
    }

    /**
     * Load the default values
     * @return void
     */
    private function loadDefaultValues(): void
    {
        $this->values = [];
        foreach ($this->getDefinitions() as $definition) {
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
        $definitions = $this->getDefinitions();

        foreach ($definitions as $definition) {
            $this->values[$definition->getCode()] = $this->fieldList->prepareValue(
                $definition,
                $this->values[$definition->getCode()]
            );
        }
    }
}
