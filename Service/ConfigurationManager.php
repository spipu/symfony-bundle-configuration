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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class ConfigurationManager
{
    private HasherFactory $hasherFactory;
    private EncryptorInterface $encryptor;
    private FieldList $fieldList;
    private Definitions $definitions;
    private Storage $storage;
    private FileManagerInterface $fileManager;
    private ?PasswordHasherInterface $hasher = null;

    public function __construct(
        HasherFactory $hasherFactory,
        EncryptorInterface $encryptor,
        FieldList $fieldList,
        Definitions $definitions,
        Storage $storage,
        FileManagerInterface $fileManager
    ) {
        $this->hasherFactory = $hasherFactory;
        $this->encryptor = $encryptor;
        $this->fieldList = $fieldList;
        $this->definitions = $definitions;
        $this->storage = $storage;
        $this->fileManager = $fileManager;
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
        $definition = $this->getDefinition($key);
        if ($definition->getType() !== 'file') {
            throw new ConfigurationException('This configuration is not a file!');
        }

        $scope = $this->storage->validateScope($scope);
        if ($scope === 'global') {
            $scope = null;
        }

        if ($scope !== null && !$definition->isScoped()) {
            throw new ConfigurationException('This configuration key is not scoped');
        }

        if (!$this->fileManager->isAllowed()) {
            throw new ConfigurationException('Configuration Files are not allowed');
        }

        $fileTypes = $definition->getFileTypes();
        $guessExtension = $file->guessExtension();
        if (
            !empty($fileTypes)
            && ($guessExtension === null || !in_array(strtolower($guessExtension), $fileTypes, true))
        ) {
            throw new ConfigurationException('File extension not allowed: ' . $guessExtension);
        }

        $fileName = $this->fileManager->saveFile($definition, $scope ?? 'global', $file);
        $this->set($key, $fileName, $scope);
    }

    public function getFile(string $key, ?string $scope = null): ?string
    {
        $definition = $this->getDefinition($key);
        if ($definition->getType() !== 'file') {
            throw new ConfigurationException('This configuration is not a file!');
        }

        $scope = $this->storage->validateScope($scope);
        if ($scope === 'global') {
            $scope = null;
        }

        if (!$this->fileManager->isAllowed()) {
            throw new ConfigurationException('Configuration Files are not allowed');
        }

        $filename = $this->get($key, $scope);
        if ($filename === null) {
            return null;
        }
        $filename = (string) $filename;
        if ($filename === '') {
            return null;
        }

        return $this->fileManager->loadFile($definition, $scope ?? 'global', $filename);
    }

    public function clearCache(): void
    {
        $this->storage->cleanValues();
    }
}
