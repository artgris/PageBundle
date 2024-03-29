<?php

namespace Artgris\Bundle\PageBundle\Service;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PageService
{
    private $blocks;
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    /**
     * PageService constructor.
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public function getBlocks()
    {
        if (null === $this->blocks && $request = $this->requestStack->getCurrentRequest()) {
            $_controller = $request->attributes->get('_controller');
            if (\is_string($_controller)) {
                $pageBlock = $this->em->getRepository(ArtgrisPage::class)->findByRoute($_controller);
                foreach ($pageBlock as $page) {
                    foreach ($page->getBlocks() as $pageBlock) {
                        $this->blocks[] = $pageBlock;
                    }
                }
            }
        }

        return $this->blocks;
    }

    public function getBlocksByName(string $bloc): ?ArtgrisBlock
    {
        return $this->em->getRepository(ArtgrisBlock::class)->findOneBy(['slug' => $bloc]);
    }

    public function getBlocksByRegex(string $bloc): ?array
    {
        return $this->em->getRepository(ArtgrisBlock::class)->findByRegex($bloc);
    }

    public function getPageBySlug(string $page): ?ArtgrisPage
    {
        return $this->em->getRepository(ArtgrisPage::class)->findOneBy(['slug' => $page]);
    }
}
