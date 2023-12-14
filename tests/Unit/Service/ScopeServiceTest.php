<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Entity\Scope;
use Spipu\ConfigurationBundle\Exception\ConfigurationScopeException;
use Spipu\ConfigurationBundle\Service\ScopeList;
use Spipu\ConfigurationBundle\Service\ScopeService;
use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;

class ScopeServiceTest extends TestCase
{
    public function testOkEmpty()
    {
        $scopeList = new ScopeList();
        $scopeService = new ScopeService($scopeList);

        $this->assertFalse($scopeService->hasScopes());
        $this->assertEmpty($scopeService->getScopes());
        $this->assertNull($scopeService->getScope(null));

        $this->expectException(ConfigurationScopeException::class);
        $scopeService->getScope('foo');
    }

    public function testOk()
    {
        $scopeList = SpipuConfigurationMock::getScopeListMock([
            new Scope('foo', 'Foo'),
            new Scope('bar', 'Bar'),
        ]);
        $scopeService = new ScopeService($scopeList);

        $this->assertTrue($scopeService->hasScopes());
        $this->assertSame(2, count($scopeService->getScopes()));
        $this->assertNull($scopeService->getScope(null));

        $this->assertSame('foo', $scopeService->getScope('foo')->getCode());
        $this->assertSame('Foo', $scopeService->getScope('foo')->getName());
        $this->assertSame('bar', $scopeService->getScope('bar')->getCode());
        $this->assertSame('Bar', $scopeService->getScope('bar')->getName());

        $this->expectException(ConfigurationScopeException::class);
        $scopeService->getScope('fake');
    }

    public function testKoDouble()
    {
        $scopeList = SpipuConfigurationMock::getScopeListMock([
            new Scope('foo', 'Foo'),
            new Scope('foo', 'Bar'),
        ]);
        $scopeService = new ScopeService($scopeList);

        $this->expectException(ConfigurationScopeException::class);
        $this->expectExceptionMessage('configuration scope code already existing');
        $scopeService->hasScopes();
    }
}
