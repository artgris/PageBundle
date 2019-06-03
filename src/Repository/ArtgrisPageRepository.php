<?php

namespace Artgris\Bundle\PageBundle\Repository;

use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ArtgrisPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtgrisPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtgrisPage[]    findAll()
 * @method ArtgrisPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtgrisPageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ArtgrisPage::class);
    }

    /**
     * @param string|null $route
     *
     * @return ArtgrisPage[]
     */
    public function findByRoute(string $route = null): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.route = :route')
            ->orWhere('p.route IS NULL')
            ->setParameter('route', $route)
            ->getQuery()
            ->getResult();
    }

}
