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
        return $this->createQueryBuilder('artgris_block')
            ->where('REGEXP(artgris_block.slug, :regexp) = true')
            ->setParameter('regexp', $bloc)
            ->orderBy('artgris_block.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
