<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Service\ScopeList;
use Spipu\ConfigurationBundle\Service\ScopeListInterface;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(ScopeList::class)]
class ScopeListTest extends TestCase
{
    public function testGetAll(): void
    {
        $scopeList = new ScopeList();

        $this->assertInstanceOf(ScopeListInterface::class, $scopeList);
        $this->assertSame([], $scopeList->getAll());
    }
}
