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

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Field\FieldInterface;
use Spipu\CoreBundle\Service\HasherFactory;
use Spipu\CoreBundle\Service\EncryptorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class ConfigurationManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var HasherFactory
     */
    private $hasherFactory;

    /**
     * @var PasswordHasherInterface
     */
    private $hasher;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var FieldList
     */
    private $fieldList;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Definitions
     */
    private $definitions;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * Configuration constructor.
     * @param ContainerInterface $container
     * @param HasherFactory $hasherFactory
     * @param EncryptorInterface $encryptor
     * @param FieldList $fieldList
     * @param Definitions $definitions
     * @param Storage $storage
     */
    public function __construct(
        ContainerInterface $container,
        HasherFactory $hasherFactory,
        EncryptorInterface $encryptor,
        FieldList $fieldList,
        Definitions $definitions,
        Storage $storage
    ) {
        $this->container = $container;
        $this->hasherFactory = $hasherFactory;
        $this->encryptor = $encryptor;
        $this->fieldList = $fieldList;
        $this->definitions = $definitions;
        $this->storage = $storage;
    }

    /**
     * Get the configuration definitions
     * @return Definition[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions->getAll();
    }

    /**
     * Get the configuration definition of a specific key
     * @param string $key
     * @return Definition
     * @throws ConfigurationException
     */
    public function getDefinition(string $key): Definition
    {
        return $this->definitions->get($key);
    }

    /**
     * @param string $key
     * @return FieldInterface
     * @throws ConfigurationException
     */
    public function getField(string $key): FieldInterface
    {
        $definition = $this->definitions->get($key);
        return $this->fieldList->getField($definition);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->storage->getAll();
    }

    /**
     * Get a configuration value
     * @param string $key
     * @return mixed
     * @throws ConfigurationException
     */
    public function get(string $key)
    {
        return $this->storage->get($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws ConfigurationException
     */
    public function set(string $key, $value): void
    {
        $this->storage->set($key, $value);
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

        return $this->getHasher()->verify($encoded, $raw);
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
            $value = $this->getHasher()->hash($value);
        }

        $this->set($key, $value);
    }

    /**
     * @return PasswordHasherInterface
     */
    private function getHasher(): PasswordHasherInterface
    {
        if (!$this->hasher) {
            $this->hasher = $this->hasherFactory->create();
        }

        return $this->hasher;
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

        $this->set($key, $this->encryptor->encrypt($value));
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
            throw new ConfigurationException('File extension not allowed: ' . $guessExtension);
        }

        $path = $this->getFilePath();
        if (!is_dir($path)  || !is_writable($path)) {
            throw new ConfigurationException('The file path does not exist or is not writable: ' . $path);
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
     * @return void
     */
    public function clearCache(): void
    {
        $this->storage->cleanValues();
    }
}
