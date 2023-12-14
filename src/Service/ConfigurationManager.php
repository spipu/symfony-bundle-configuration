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

use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\CoreBundle\Service\HasherFactory;
use Spipu\CoreBundle\Service\EncryptorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class ConfigurationManager extends BasicConfigurationManager
{
    private HasherFactory $hasherFactory;
    private EncryptorInterface $encryptor;
    private FileManagerInterface $fileManager;
    private ?PasswordHasherInterface $hasher = null;

    public function __construct(
        FieldList $fieldList,
        Definitions $definitions,
        Storage $storage,
        HasherFactory $hasherFactory,
        EncryptorInterface $encryptor,
        FileManagerInterface $fileManager
    ) {
        parent::__construct($fieldList, $definitions, $storage);

        $this->hasherFactory = $hasherFactory;
        $this->encryptor = $encryptor;
        $this->fileManager = $fileManager;
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

    /**
     * @param string $key
     * @param UploadedFile|null $file
     * @param string|null $scope
     * @return void
     * @throws ConfigurationException
     * @SuppressWarnings(PMD.CyclomaticComplexity)
     * @SuppressWarnings(PMD.NPathComplexity)
     */
    public function setFile(string $key, ?UploadedFile $file, ?string $scope = null): void
    {
        $definition = $this->getDefinition($key);
        if ($definition->getType() !== 'file') {
            throw new ConfigurationException('This configuration is not a file!');
        }

        $scope = $this->validateScope($scope);
        if ($scope === 'global') {
            $scope = null;
        }

        if ($scope !== null && !$definition->isScoped()) {
            throw new ConfigurationException('This configuration key is not scoped');
        }

        if (!$this->fileManager->isAllowed()) {
            throw new ConfigurationException('Configuration Files are not allowed');
        }

        if ($file === null && $definition->isRequired()) {
            throw new ConfigurationException('This Configuration is required');
        }

        if ($file !== null) {
            $fileTypes = $definition->getFileTypes();
            $guessExtension = $file->guessExtension();
            if (
                !empty($fileTypes)
                && ($guessExtension === null || !in_array(strtolower($guessExtension), $fileTypes, true))
            ) {
                throw new ConfigurationException('File extension not allowed: ' . $guessExtension);
            }
        }

        $oldFilename = $this->get($key, $scope);
        if ($oldFilename !== null && (string) $oldFilename !== '') {
            $this->fileManager->removeFile($definition, $scope ?? 'global', (string) $oldFilename);
            if (!$definition->isRequired()) {
                $this->set($key, null, $scope);
            }
        }

        if ($file !== null) {
            $fileName = $this->fileManager->saveFile($definition, $scope ?? 'global', $file);
            $this->set($key, $fileName, $scope);
        }
    }

    public function getFilePath(string $key, ?string $scope = null): ?string
    {
        return $this->getFile($key, $scope, [$this->fileManager, 'getFilePath']);
    }

    public function getFileUrl(string $key, ?string $scope = null): ?string
    {
        return $this->getFile($key, $scope, [$this->fileManager, 'getFileUrl']);
    }

    private function getFile(string $key, ?string $scope, callable $fileCallback): ?string
    {
        $definition = $this->getDefinition($key);
        if ($definition->getType() !== 'file') {
            throw new ConfigurationException('This configuration is not a file!');
        }

        $scope = $this->validateScope($scope);
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

        return call_user_func_array($fileCallback, [$definition, $scope ?? 'global', $filename]);
    }
}
