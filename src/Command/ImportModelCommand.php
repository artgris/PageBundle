<?php

namespace Artgris\Bundle\PageBundle\Command;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ImportPageCommand.
 */
class ImportModelCommand extends Command
{
    private const REMOVE_DEVIANTS = 'remove-deviants';
    protected static $defaultName = 'page:import';
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

    protected function configure()
    {
        $this
            ->addOption(self::REMOVE_DEVIANTS, null, InputOption::VALUE_NONE, 'Delete the content of types that have changed');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pageRepository = $this->em->getRepository(ArtgrisPage::class);
        $blockRepository = $this->em->getRepository(ArtgrisBlock::class);

        $pages = Yaml::parseFile($this->kernel->getProjectDir() . ExportModelCommand::YAML_ROUTE);

        foreach ($pages as $pageName => $page) {
            $pageEntity = $pageRepository->findOneBy(['slug' => $page['slug']]) ?? new ArtgrisPage();

            $pageEntity->setSlug($page['slug']);
            $pageEntity->setRoute($page['route']);
            $pageEntity->setName($pageName);
            $this->em->persist($pageEntity);

            $position = 0;
            foreach ($page['blocks'] as $blockName => $block) {
                $blockEntity = $blockRepository->findOneBy(['slug' => $block['slug']]) ?? new ArtgrisBlock();
                $blockEntity->setSlug($block['slug']);
                $blockEntity->setPage($pageEntity);
                $blockEntity->setPosition($position);
                $blockEntity->setName($blockName);

                if ($blockEntity->getType() !== $block['type']) {
                    $blockEntity->setType($block['type']);

                    if ($input->getOption(self::REMOVE_DEVIANTS)) {
                        $blockEntity->setContent(null);
                        // delete translations
                        if ($blockEntity->getTranslations()) {
                            foreach ($blockEntity->getTranslations() as $translation) {
                                $blockEntity->removeTranslation($translation);
                            }
                        }
                    }
                }

                $blockEntity->setTranslatable($block['translatable']);
                $this->em->persist($blockEntity);

                $position++;
            }
        }

        // confirmation before flush
        $this->em->flush();
    }
}
