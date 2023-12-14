<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Form\Options;

use Spipu\ConfigurationBundle\Entity\Scope;
use Spipu\ConfigurationBundle\Form\Options\ScopeOptions;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Tests\ConfigurationScopeListMock;

class ScopeOptionsTest extends TestCase
{
    public function testBaseEmpty()
    {
        $scopeList = new ConfigurationScopeListMock();
        $scopeList->set([]);
        $options = new ScopeOptions($scopeList);
        $this->assertSame([], $options->getOptions());
    }

    public function testBaseFull()
    {
        $scopeList = new ConfigurationScopeListMock();
        $scopeList->set(
            [
                new Scope('aa', 'Aaa'),
                new Scope('bb', 'bBbb'),
            ]
        );

        $options = new ScopeOptions($scopeList);
        $this->assertSame(['aa' => 'Aaa', 'bb' => 'bBbb'], $options->getOptions());
    }
}
