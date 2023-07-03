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
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager implements FileManagerInterface
{
    private bool $allowFiles;
    private string $projectDir;
    private string $filePath;
    private string $fileUrl;
    private ?string $fullFilePath = null;

    public function __construct(
        bool $allowFiles,
        string $projectDir,
        string $filePath,
        string $fileUrl,
    ) {
        $this->allowFiles = $allowFiles;
        $this->projectDir = $projectDir;
        $this->filePath = $filePath;
        $this->fileUrl = $fileUrl;
    }

    public function isAllowed(): bool
    {
        return $this->allowFiles;
    }

    private function getFolderPath(): string
    {
        if ($this->fullFilePath === null) {
            $this->fullFilePath = rtrim($this->projectDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .
                rtrim($this->filePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
        return $this->fullFilePath;
    }

    private function getFolderUrl(): string
    {
        return '/' . trim($this->fileUrl, '/') . '/';
    }

    public function saveFile(Definition $definition, string $scope, UploadedFile $file): string
    {
        $path = $this->getFolderPath();
        if (!is_dir($path)  || !is_writable($path)) {
            throw new ConfigurationException('The file path does not exist or is not writable: ' . $path);
        }

        $fileName  = md5($definition->getCode()) . '.' . $file->guessExtension();
        $this->validateFilename($fileName);

        $file->move($path, $scope . '_' . $fileName);

        return $fileName;
    }

    public function removeFile(Definition $definition, string $scope, string $filename): void
    {
        $this->validateFilename($filename);

        $filePath = $this->getFilePath($definition, $scope, $filename);
        if ($filePath !== null && is_file($filePath)) {
            unlink($filePath);
        }
    }

    public function getFilePath(Definition $definition, string $scope, string $filename): ?string
    {
        $this->validateFilename($filename);

        $filename = $scope . '_' . $filename;
        $filePath = $this->getFolderPath() . $filename;

        if (!is_file($filePath)) {
            return null;
        }

        return $filePath;
    }

    public function getFileUrl(Definition $definition, string $scope, string $filename): ?string
    {
        $this->validateFilename($filename);

        if ($this->getFilePath($definition, $scope, $filename) === null) {
            return null;
        }

        return $this->getFolderUrl() . $scope . '_' . $filename;
    }

    private function validateFilename(string $filename): void
    {
        if ($filename !== str_replace(['/', '\\'], '', $filename)) {
            throw new ConfigurationException('This filename is not allowed');
        }
    }
}
