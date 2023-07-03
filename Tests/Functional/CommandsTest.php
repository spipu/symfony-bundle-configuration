<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spipu\ConfigurationBundle\Tests\Functional;

use Spipu\ConfigurationBundle\Command\ClearCacheCommand;
use Spipu\ConfigurationBundle\Command\DeleteCommand;
use Spipu\ConfigurationBundle\Command\EditCommand;
use Spipu\ConfigurationBundle\Command\ScopeCommand;
use Spipu\ConfigurationBundle\Command\ShowCommand;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\CoreBundle\Tests\WebTestCase;

class CommandsTest extends WebTestCase
{
    public function testScope()
    {
        $commandTester = self::loadCommand(
            ScopeCommand::class,
            'spipu:configuration:scope'
        );

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Is using scopes: Yes', $output);
        $this->assertStringContainsString('Scopes', $output);
        $this->assertStringContainsString('- Global', $output);
        $this->assertStringContainsString('- [fr] Français', $output);
        $this->assertStringContainsString('- [en] English', $output);
    }

    public function testShowOkAll()
    {
        $commandTester = self::loadCommand(ShowCommand::class, 'spipu:configuration:show');

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Show All Configurations', $output);
        $output = preg_replace('/ +/', ' ', $output);
        $this->assertStringContainsString('| code | type | required | scoped | value |', $output);
        $this->assertStringContainsString('| app.website.name | string | yes | no | Symfony Dev |', $output);
        $this->assertStringContainsString('| test.type.text | string | yes | yes | My text |', $output);
    }

    public function testShowOkOne()
    {
        $commandTester = self::loadCommand(ShowCommand::class, 'spipu:configuration:show');

        $commandTester->execute(['--key' => 'test.type.text', '--scope' => 'global']);
        $output = $commandTester->getDisplay();
        $output = preg_replace('/ +/', ' ', $output);
        $this->assertStringContainsString('| code | type | required | scoped | value |', $output);
        $this->assertStringContainsString('| test.type.text | string | yes | yes | My text |', $output);

    }

    public function testShowOkOneOtherCases()
    {
        $this->assertSame('', $this->getConfigurationValue('test.type.file', 'global'));
        $this->assertSame('1', $this->getConfigurationValue('test.type.select', 'global'));
    }

    public function testShowKoNotExist()
    {
        $commandTester = self::loadCommand(ShowCommand::class, 'spipu:configuration:show');

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Unknown configuration key [bad key]');
        $commandTester->execute(['--key' => 'bad key', '--scope' => 'global']);
    }

    public function testShowKoNotScoped()
    {
        $commandTester = self::loadCommand(ShowCommand::class, 'spipu:configuration:show');

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('This configuration key is not scoped');
        $commandTester->execute(['--key' => 'app.website.name', '--scope' => 'fr']);
    }

    public function testShowKoUnknownScope()
    {
        $commandTester = self::loadCommand(ShowCommand::class, 'spipu:configuration:show');

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Unknown configuration scope [foo]');
        $commandTester->execute(['--key' => 'test.type.text', '--scope' => 'foo']);
    }

    public function testEditKoFile()
    {
        $commandTester = self::loadCommand(EditCommand::class, 'spipu:configuration:edit');

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Unable to set a file in CLI');
        $commandTester->execute(['--key' => 'test.type.file', '--scope' => 'global', '--value' => 'foo']);
    }

    public function testCacheClearOk()
    {
        $commandTester = self::loadCommand(ClearCacheCommand::class, 'spipu:configuration:clear-cache');

        $commandTester->execute([]);
        $output = trim($commandTester->getDisplay());
        $this->assertStringContainsString('=> OK', $output);
    }

    public function testAll()
    {
        $this->assertSame('My text', $this->getConfigurationValue('test.type.text', 'default'));
        $this->assertSame('My text', $this->getConfigurationValue('test.type.text', 'global'));
        $this->assertSame('My text', $this->getConfigurationValue('test.type.text', 'fr'));

        $this->setConfigurationValue('test.type.text', 'global', 'My global');

        $this->assertSame('My text', $this->getConfigurationValue('test.type.text', 'default'));
        $this->assertSame('My global', $this->getConfigurationValue('test.type.text', 'global'));
        $this->assertSame('My global', $this->getConfigurationValue('test.type.text', 'fr'));

        $this->setConfigurationValue('test.type.text', 'fr', 'My french');

        $this->assertSame('My text', $this->getConfigurationValue('test.type.text', 'default'));
        $this->assertSame('My global', $this->getConfigurationValue('test.type.text', 'global'));
        $this->assertSame('My french', $this->getConfigurationValue('test.type.text', 'fr'));

        $this->delConfigurationValue('test.type.text', 'global');

        $this->assertSame('My text', $this->getConfigurationValue('test.type.text', 'default'));
        $this->assertSame('My text', $this->getConfigurationValue('test.type.text', 'global'));
        $this->assertSame('My french', $this->getConfigurationValue('test.type.text', 'fr'));

        $this->delConfigurationValue('test.type.text', 'fr');

        $this->assertSame('My text', $this->getConfigurationValue('test.type.text', 'default'));
        $this->assertSame('My text', $this->getConfigurationValue('test.type.text', 'global'));
        $this->assertSame('My text', $this->getConfigurationValue('test.type.text', 'fr'));

        $this->assertSame('0', $this->getConfigurationValue('test.type.boolean', 'global'));
    }

    private function getConfigurationValue(string $key, string $scope): string
    {
        $commandTester = self::loadCommand(ShowCommand::class, 'spipu:configuration:show');

        $commandTester->execute(['--key' => $key, '--scope' => $scope, '--direct' => true]);
        return trim($commandTester->getDisplay());
    }

    private function setConfigurationValue(string $key, string $scope, string $value): void
    {
        $commandTester = self::loadCommand(EditCommand::class, 'spipu:configuration:edit');

        $commandTester->execute(['--key' => $key, '--scope' => $scope, '--value' => $value]);
        $output = trim($commandTester->getDisplay());
        $this->assertStringContainsString('=> done', $output);
    }

    private function delConfigurationValue(string $key, string $scope): void
    {
        $commandTester = self::loadCommand(DeleteCommand::class, 'spipu:configuration:delete');

        $commandTester->execute(['--key' => $key, '--scope' => $scope]);
        $output = trim($commandTester->getDisplay());
        $this->assertStringContainsString('=> done', $output);
    }
}
