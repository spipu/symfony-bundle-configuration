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

    private function getFilePath(): string
    {
        if ($this->fullFilePath === null) {
            $this->fullFilePath = rtrim($this->projectDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .
                rtrim($this->filePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
        return $this->fullFilePath;
    }

    private function getFileUrl(): string
    {
        return $this->fileUrl;
    }

    public function saveFile(Definition $definition, string $scope, UploadedFile $file): string
    {
        $path = $this->getFilePath();
        if (!is_dir($path)  || !is_writable($path)) {
            throw new ConfigurationException('The file path does not exist or is not writable: ' . $path);
        }

        $fileName  = md5($definition->getCode()) . '.' . $file->guessExtension();
        $file->move($path, $scope . '_' . $fileName);

        return $fileName;
    }

    public function loadFile(Definition $definition, string $scope, string $filename): ?string
    {
        $filename = $scope . '_' . $filename;

        $filePath = $this->getFilePath() . $filename;
        $fileUrl  = '/' . $this->getFileUrl() . $filename;

        if (!is_file($filePath)) {
            return null;
        }

        return $fileUrl;
    }
}
