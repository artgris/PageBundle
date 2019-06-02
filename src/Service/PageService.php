<?php

namespace Artgris\Bundle\PageBundle\Service;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PageService
{
    private $blocks;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var EntityManagerInterface
     */
    private $em;

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

            $pageBlock = $this->em->getRepository(ArtgrisPage::class)->findByRoute($request->get('_controller'));
            foreach ($pageBlock as $page) {
                foreach ($page->getBlocks() as $pageBlock) {
                    $this->blocks[] = $pageBlock;
                }
            }
        }

        return $this->blocks;
    }

    public function getBlocksByName(string $bloc)
    {
        return $this->em->getRepository(ArtgrisBlock::class)->findOneBy(['slug' => $bloc]);
    }

}
