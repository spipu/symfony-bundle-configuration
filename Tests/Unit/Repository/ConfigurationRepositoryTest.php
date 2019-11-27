<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Spipu\ConfigurationBundle\Repository\ConfigurationRepository;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;

class ConfigurationRepositoryTest extends TestCase
{
    public function testRepository()
    {
        $repository = new ConfigurationRepository(SymfonyMock::getEntityRegistry($this));

        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }
}
