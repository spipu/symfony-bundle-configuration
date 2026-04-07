<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Service\FileManager;
use Spipu\ConfigurationBundle\Service\FileManagerInterface;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(FileManager::class)]
class FileManagerTest extends TestCase
{
    private ?string $tmpDir = null;

    protected function tearDown(): void
    {
        if ($this->tmpDir !== null && is_dir($this->tmpDir)) {
            $filesDir = $this->tmpDir . '/files';
            if (is_dir($filesDir)) {
                $files = glob($filesDir . '/*');
                foreach ($files as $file) {
                    unlink($file);
                }
                rmdir($filesDir);
            }
            rmdir($this->tmpDir);
        }
    }

    private function getTmpDir(): string
    {
        if ($this->tmpDir === null) {
            $this->tmpDir = sys_get_temp_dir() . '/spipu_file_manager_test_' . uniqid();
            mkdir($this->tmpDir . '/files', 0777, true);
        }
        return $this->tmpDir;
    }

    private function createDefinition(): Definition
    {
        return new Definition('category.key', 'file', false, false, null, null, null, null, ['png']);
    }

    public static function getService(bool $allow = true): FileManagerInterface
    {
        return new FileManager(
            $allow,
            '/mock/project/',
            'media/foo/',
            'foo/',
        );
    }

    private function getRealService(): FileManager
    {
        return new FileManager(
            true,
            $this->getTmpDir(),
            'files',
            'uploads/',
        );
    }

    public function testBase(): void
    {
        $fileManager = self::getService();

        $this->assertSame(true, $fileManager->isAllowed());
    }

    public function testIsAllowedFalse(): void
    {
        $fileManager = self::getService(false);

        $this->assertSame(false, $fileManager->isAllowed());
    }

    public function testGetFilePathNotFound(): void
    {
        $fileManager = $this->getRealService();
        $definition = $this->createDefinition();

        $this->assertNull($fileManager->getFilePath($definition, 'default', 'nonexistent.png'));
    }

    public function testGetFilePathFound(): void
    {
        $tmpDir = $this->getTmpDir();
        file_put_contents($tmpDir . '/files/default_test.png', 'content');

        $fileManager = $this->getRealService();
        $definition = $this->createDefinition();

        $result = $fileManager->getFilePath($definition, 'default', 'test.png');
        $this->assertSame($tmpDir . '/files/default_test.png', $result);
    }

    public function testGetFileUrlNotFound(): void
    {
        $fileManager = $this->getRealService();
        $definition = $this->createDefinition();

        $this->assertNull($fileManager->getFileUrl($definition, 'default', 'nonexistent.png'));
    }

    public function testGetFileUrlFound(): void
    {
        $tmpDir = $this->getTmpDir();
        file_put_contents($tmpDir . '/files/default_test.png', 'content');

        $fileManager = $this->getRealService();
        $definition = $this->createDefinition();

        $result = $fileManager->getFileUrl($definition, 'default', 'test.png');
        $this->assertSame('/uploads/default_test.png', $result);
    }

    public function testRemoveFileNotExisting(): void
    {
        $fileManager = $this->getRealService();
        $definition = $this->createDefinition();

        $fileManager->removeFile($definition, 'default', 'nonexistent.png');
        $this->assertTrue(true);
    }

    public function testRemoveFileExisting(): void
    {
        $tmpDir = $this->getTmpDir();
        $filePath = $tmpDir . '/files/default_test.png';
        file_put_contents($filePath, 'content');
        $this->assertFileExists($filePath);

        $fileManager = $this->getRealService();
        $definition = $this->createDefinition();

        $fileManager->removeFile($definition, 'default', 'test.png');
        $this->assertFileDoesNotExist($filePath);
    }

    public function testValidateFilenameWithSlash(): void
    {
        $fileManager = $this->getRealService();
        $definition = $this->createDefinition();

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('This filename is not allowed');
        $fileManager->getFilePath($definition, 'default', '../etc/passwd');
    }

    public function testValidateFilenameWithBackslash(): void
    {
        $fileManager = $this->getRealService();
        $definition = $this->createDefinition();

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('This filename is not allowed');
        $fileManager->getFilePath($definition, 'default', '..\\etc\\passwd');
    }

    public function testSaveFilePathNotWritable(): void
    {
        $fileManager = new FileManager(true, '/nonexistent/path', 'media/', 'media/');
        $definition = $this->createDefinition();

        $uploadedFile = $this->createMock(\Symfony\Component\HttpFoundation\File\UploadedFile::class);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('The file path does not exist or is not writable');
        $fileManager->saveFile($definition, 'default', $uploadedFile);
    }
}
