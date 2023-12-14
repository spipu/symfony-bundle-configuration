<?php

namespace Spipu\ConfigurationBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Service\FileManager;
use Spipu\ConfigurationBundle\Service\FileManagerInterface;

class FileManagerTest extends TestCase
{
    public static function getService(bool $allow = true): FileManagerInterface
    {
        return new FileManager(
            $allow,
            '/mock/project/',
            'media/foo/',
            'foo/',
        );
    }

    public function testBase()
    {
        $fileManager = self::getService();

        $this->assertSame(true, $fileManager->isAllowed());
    }
}
