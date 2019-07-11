<?php
namespace Spipu\ConfigurationBundle\Tests\Unit\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Spipu\ConfigurationBundle\Repository\ConfigurationRepository;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ConfigurationRepositoryTest extends TestCase
{
    public function testRepository()
    {
        /** @var RegistryInterface $registry */
        $repository = new ConfigurationRepository(SymfonyMock::getEntityRegistry($this));

        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }
}
