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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class ConfigurationManager extends BasicConfigurationManager
{
    /**
     * @var HasherFactory
     */
    private $hasherFactory;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var PasswordHasherInterface|null
     */
    private $hasher = null;

    /**
     * @var string
     */
    private $filePath;

    /**
     * Configuration constructor.
     * @param FieldList $fieldList
     * @param Definitions $definitions
     * @param Storage $storage
     * @param HasherFactory $hasherFactory
     * @param EncryptorInterface $encryptor
     * @param ContainerInterface $container
     */
    public function __construct(
        FieldList $fieldList,
        Definitions $definitions,
        Storage $storage,
        HasherFactory $hasherFactory,
        EncryptorInterface $encryptor,
        ContainerInterface $container
    ) {
        parent::__construct($fieldList, $definitions, $storage);

        $this->hasherFactory = $hasherFactory;
        $this->encryptor = $encryptor;
        $this->container = $container;
    }



    /**
     * @param string $key
     * @param string $raw
     * @param string|null $scope
     * @return bool
     * @throws ConfigurationException
     */
    public function isPasswordValid(string $key, string $raw, ?string $scope = null): bool
    {
        $encoded = $this->get($key, $scope);

        return $this->getHasher()->verify($encoded, $raw);
    }

    /**
     * @param string $key
     * @param ?string $value
     * @param string|null $scope
     * @return void
     * @throws ConfigurationException
     */
    public function setPassword(string $key, ?string $value, ?string $scope = null): void
    {
        if ($value !== null) {
            $value = $this->getHasher()->hash($value);
        }

        $this->set($key, $value, $scope);
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
     * @param string|null $scope
     * @return string|null
     * @throws ConfigurationException
     */
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

    /**
     * @param string $key
     * @param ?string $value
     * @param string|null $scope
     * @return void
     * @throws ConfigurationException
     */
    public function setEncrypted(string $key, ?string $value, ?string $scope = null): void
    {
        if ($value !== null) {
            $value = $this->encryptor->encrypt($value);
        }

        $this->set($key, $value, $scope);
    }

    /**
     * @param string $key
     * @param UploadedFile $file
     * @param string|null $scope
     * @return void
     * @throws ConfigurationException
     */
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
}
