<?php

namespace Artgris\Bundle\PageBundle\Tests\Fixtures\AppTestBundle\DataFixtures;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Artgris\Bundle\PageBundle\Service\TypeService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * @var TypeService
     */
    private $typeService;

    /**
     * AppFixtures constructor.
     */
    public function __construct(TypeService $typeService)
    {
        $this->typeService = $typeService;
    }


    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $pages = $this->createPages();
        $blocks = $this->createBlocks($pages);

        foreach ($pages as $page) {
            $manager->persist($page);
        }
        foreach ($blocks as $block) {
            $manager->persist($block);
        }
        $manager->flush();
    }

    private function createPages(): array
    {
        $pages = [];
        foreach (\range(1, 20) as $i) {
            $page = new ArtgrisPage();
            $page->setName($this->getRandomName());
            $pages[] = $page;
        }

        return $pages;
    }

    private function createBlocks(array $pages)
    {
        $types = $this->typeService->getTypes();
        $blocks = [];
        foreach (\range(1, 30) as $i) {
            $numItemsBlocks = \random_int(1, 5);
            foreach (\range(1, $numItemsBlocks) as $j) {
                $block = new ArtgrisBlock();
                $block->setName($this->getRandomName('blok'));
                $block->setPage($pages[\array_rand($pages)]);
                $block->setContent('"Hello"');
                $block->setPosition($j -1);
                $block->setType($types[\array_rand($types)]);
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    public function getRandomName($name = "Page")
    {
        $words = [
            'Lorem', 'Ipsum', 'Sit', 'Amet', 'Adipiscing', 'Elit',
            'Vitae', 'Velit', 'Mauris', 'Dapibus', 'Suscipit', 'Vulputate',
            'Eros', 'Diam', 'Egestas', 'Libero', 'Platea', 'Dictumst',
            'Tempus', 'Commodo', 'Mattis', 'Donec', 'Posuere', 'Eleifend',
        ];
        $numWords = 2;
        \shuffle($words);
        return $name.' '.\implode(' ', \array_slice($words, 0, $numWords));
    }
}
