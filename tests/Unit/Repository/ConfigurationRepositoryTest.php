<?php

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Tests\Unit\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\ConfigurationBundle\Repository\ConfigurationRepository;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(ConfigurationRepository::class)]
class ConfigurationRepositoryTest extends TestCase
{
    public function testRepository(): void
    {
        $repository = new ConfigurationRepository(SymfonyMock::getEntityRegistry($this));

        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }
}
