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
    private ContainerInterface $container;
    private HasherFactory $hasherFactory;
    private EncryptorInterface $encryptor;
    private FieldList $fieldList;
    private Definitions $definitions;
    private Storage $storage;
    private ?string $filePath = null;
    private ?PasswordHasherInterface $hasher = null;

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
     * @return Definition[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions->getAll();
    }

    public function getDefinition(string $key): Definition
    {
        return $this->definitions->get($key);
    }

    public function getField(string $key): FieldInterface
    {
        $definition = $this->definitions->get($key);
        return $this->fieldList->getField($definition);
    }

    public function getAll(): array
    {
        return $this->storage->getAll();
    }

    public function get(string $key, ?string $scope = null): mixed
    {
        return $this->storage->get($key, $scope);
    }

    public function set(string $key, mixed $value, ?string $scope = null): void
    {
        $this->storage->set($key, $value, $scope);
    }

    public function delete(string $key, ?string $scope = null): void
    {
        $this->storage->delete($key, $scope);
    }

    public function isPasswordValid(string $key, string $raw, ?string $scope = null): bool
    {
        $encoded = $this->get($key, $scope);

        return $this->getHasher()->verify($encoded, $raw);
    }

    public function setPassword(string $key, ?string $value, ?string $scope = null): void
    {
        if ($value !== null) {
            $value = $this->getHasher()->hash($value);
        }

        $this->set($key, $value, $scope);
    }

    private function getHasher(): PasswordHasherInterface
    {
        if (!$this->hasher) {
            $this->hasher = $this->hasherFactory->create();
        }

        return $this->hasher;
    }

    public function getEncrypted(string $key, ?string $scope = null): ?string
    {
        $value = $this->get($key, $scope);
        if ($value === null) {
            return null;
        }
        if ($value === '') {
            return '';
        }

        return $this->encryptor->decrypt((string) $value);
    }

    public function setEncrypted(string $key, ?string $value, ?string $scope = null): void
    {
        if ($value !== null) {
            $value = $this->encryptor->encrypt($value);
        }

        $this->set($key, $value, $scope);
    }

    public function setFile(string $key, UploadedFile $file, ?string $scope = null): void
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

        $this->set($key, $fileName, $scope);
    }

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

    public function getFileUrl(): string
    {
        return $this->container->getParameter('spipu.configuration.file.url');
    }

    public function clearCache(): void
    {
        $this->storage->cleanValues();
    }
}
