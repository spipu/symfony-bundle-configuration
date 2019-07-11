<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Command\ShowCommand;
use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;
use Spipu\CoreBundle\Tests\SymfonyMock;

class ShowCommandTest extends TestCase
{
    public function testShowAll()
    {
        $manager = SpipuConfigurationMock::getManager($this);
        $manager->expects($this->never())->method('getDefinition');
        $manager->expects($this->once())->method('getDefinitions');
        $manager->expects($this->exactly(3))->method('get');

        $inputMock = SymfonyMock::getConsoleInput($this);

        $outputMock = SymfonyMock::getConsoleOutput($this);

        $command = new ShowCommand($manager);
        $this->assertSame('spipu:configuration:show', $command->getName());

        $command->run($inputMock, $outputMock);

        $result = SymfonyMock::getConsoleOutputResult();
        $this->assertSame('Show All Configurations', $result[0]);
    }

    public function testShowOne()
    {
        $manager = SpipuConfigurationMock::getManager($this);
        $manager->expects($this->never())->method('getDefinitions');
        $manager->expects($this->once())->method('getDefinition');
        $manager->expects($this->once())->method('get')->with('mock.string');

        $inputMock = SymfonyMock::getConsoleInput($this, [], ['key' => 'mock.string']);

        $outputMock = SymfonyMock::getConsoleOutput($this);

        $command = new ShowCommand($manager);
        $this->assertSame('spipu:configuration:show', $command->getName());

        $command->run($inputMock, $outputMock);

        $result = SymfonyMock::getConsoleOutputResult();
        $this->assertSame('Show Configuration [mock.string]', $result[0]);
    }
}
