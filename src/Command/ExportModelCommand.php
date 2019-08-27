<?php

namespace Artgris\Bundle\PageBundle\Command;

use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class ExportModelCommand extends Command
{
    protected static $defaultName = 'page:export';
    public const YAML_ROUTE = '/pages/model.yaml';
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ExportPageCommand constructor.
     */
    public function __construct(KernelInterface $kernel, EntityManagerInterface $em)
    {
        $this->kernel = $kernel;
        parent::__construct();
        $this->em = $em;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pagesEntities = $this->em->getRepository(ArtgrisPage::class)->findAll();

        $pages = [];
        foreach ($pagesEntities as $pageEntity) {
            $blocks = [];
            foreach ($pageEntity->getBlocks() as $block) {
                $blocks[$block->getName()] = [
                    'type' => $block->getType(),
                    'slug' => $block->getSlug(),
                    'translatable' => $block->isTranslatable(),
                ];
            }

            $pages[$pageEntity->getName()] = [
                'route' => $pageEntity->getRoute(),
                'slug' => $pageEntity->getSlug(),
                'blocks' => $blocks,
            ];
        }

        $yaml = Yaml::dump($pages, 5);
        file_put_contents($this->kernel->getProjectDir() . self::YAML_ROUTE, $yaml);
    }
}
