<?php


use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $pages = $this->createPages();
        foreach ($pages as $page) {
            $manager->persist($page);
        }
    }

    private function createPages(): array
    {
        $pages = [];
        foreach (\range(1, 20) as $i) {
            $page = new ArtgrisPage();
            $page->setName('page'.$i);
            $pages[] = $page;
        }

        return $pages;
    }
}
