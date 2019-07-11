<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Twig;

use Spipu\ConfigurationBundle\Service\Manager;
use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;
use Spipu\ConfigurationBundle\Twig\ConfigurationExtension;
use PHPUnit\Framework\TestCase;

class ConfigurationExtensionTest extends TestCase
{
    public function getExtension(): ConfigurationExtension
    {
        $manager = $manager = SpipuConfigurationMock::getManager($this);

        /** @var Manager $manager */
        $extension = new ConfigurationExtension($manager);

        return $extension;
    }

    public function testExtension()
    {
        $extension = $this->getExtension();
        $this->assertTrue($extension instanceof \Twig_ExtensionInterface);

        $allowedNames = [
            'get_config',
        ];
        $filters = $extension->getFilters();

        $foundNames = [];
        foreach ($filters as $filter) {
            $this->assertTrue(in_array($filter->getName(), $allowedNames));
            $this->assertTrue(call_user_func_array('method_exists', $filter->getCallable()));
            $foundNames[] = $filter->getName();
        }

        $this->assertSame(count($allowedNames), count($foundNames));
        $this->assertSame('test', $extension->getConfiguration('test'));
    }
}
