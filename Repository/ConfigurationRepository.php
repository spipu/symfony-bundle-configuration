<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Repository;

use Spipu\ConfigurationBundle\Entity\Configuration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Configuration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Configuration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Configuration[]    findAll()
 * @method Configuration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigurationRepository extends ServiceEntityRepository
{
    /**
     * ConfigurationRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Configuration::class);
    }
}
