<?php

namespace Artgris\Bundle\PageBundle\Repository;

use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArtgrisPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtgrisPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtgrisPage[]    findAll()
 * @method ArtgrisPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtgrisPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtgrisPage::class);
    }

    /**
     * @return ArtgrisPage[]
     */
    public function findByRoute(string $route = null): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('blocks')
            ->addSelect('translations')
            ->leftJoin('p.blocks', 'blocks')
            ->leftJoin('blocks.translations', 'translations')
            ->where('p.route = :route')
            ->orWhere('p.route IS NULL')
            ->setParameter('route', $route)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ArtgrisPage[]
     */
    public function findPageDiff(array $pages): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.slug not in (:pages)')
            ->setParameter('pages', $pages)
            ->getQuery()
            ->getResult();
    }
}
