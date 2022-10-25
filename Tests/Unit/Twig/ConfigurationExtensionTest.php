<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Twig;

use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;
use Spipu\ConfigurationBundle\Twig\ConfigurationExtension;
use PHPUnit\Framework\TestCase;
use Twig\Extension\ExtensionInterface;

class ConfigurationExtensionTest extends TestCase
{
    public function getExtension(): ConfigurationExtension
    {
        $manager = SpipuConfigurationMock::getManager($this);

        $extension = new ConfigurationExtension($manager);

        return $extension;
    }

    public function testExtension()
    {
        $extension = $this->getExtension();
        $this->assertTrue($extension instanceof ExtensionInterface);

        $allowedNames = [
            'get_config',
            'get_config_file_url',
        ];
        $filters = $extension->getFilters();

        $foundNames = [];
        foreach ($filters as $filter) {
            $this->assertTrue(in_array($filter->getName(), $allowedNames));
            $this->assertTrue(call_user_func_array('method_exists', $filter->getCallable()));
            $foundNames[] = $filter->getName();
        }

        $this->assertSame(count($allowedNames), count($foundNames));
        $this->assertSame('test', $extension->getValue('test'));
        $this->assertSame('/folder/mock/test.jpg', $extension->getFileUrl('test.jpg'));
    }
}
