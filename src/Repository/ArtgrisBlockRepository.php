<?php

namespace Artgris\Bundle\PageBundle\Repository;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ArtgrisBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtgrisBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtgrisBlock[]    findAll()
 * @method ArtgrisBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtgrisBlockRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ArtgrisBlock::class);
    }

    /**
     * @return ArtgrisBlock[]
     */
    public function findByRegex(string $bloc = null): array
    {
        return $this->createQueryBuilder('b')
            ->where('REGEXP(b.slug, :regexp) = true')
            ->setParameter('regexp', $bloc)
            ->orderBy('b.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $blocks
     * @return ArtgrisBlock[]
     */
    public function findBlockDiff(array $blocks): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.slug not in (:blocks)')
            ->setParameter('blocks', $blocks)
            ->getQuery()
            ->getResult();

    }
}
