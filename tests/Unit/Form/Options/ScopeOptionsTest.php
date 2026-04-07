<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Form\Options;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\ConfigurationBundle\Entity\Scope;
use Spipu\ConfigurationBundle\Form\Options\ScopeOptions;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Tests\ConfigurationScopeListMock;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(ScopeOptions::class)]
class ScopeOptionsTest extends TestCase
{
    public function testBaseEmpty(): void
    {
        $scopeList = new ConfigurationScopeListMock();
        $scopeList->set([]);
        $options = new ScopeOptions($scopeList);
        $this->assertSame([], $options->getOptions());
    }

    public function testBaseFull(): void
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
