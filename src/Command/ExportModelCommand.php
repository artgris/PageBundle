<?php

namespace Artgris\Bundle\PageBundle\Command;

use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class ExportModelCommand extends Command
{
    protected static $defaultName = 'page:export';
    public const DIRNAME = '/pages/';
    public const FILENAME = 'model.yaml';

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
        $this->em = $em;
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $pagesEntities = $this->em->getRepository(ArtgrisPage::class)->findAll();

        $pages = [];
        foreach ($pagesEntities as $pageEntity) {
            $blocks = [];
            foreach ($pageEntity->getBlocks() as $block) {
                $blocks[$block->getSlug()] = [
                    'type' => $block->getType(),
                    'name' => $block->getName(),
                    'translatable' => $block->isTranslatable(),
                ];
            }

            $pages[$pageEntity->getSlug()] = [
                'route' => $pageEntity->getRoute(),
                'name' => $pageEntity->getName(),
                'blocks' => $blocks,
            ];
        }

        $yaml = Yaml::dump($pages, 5);

        $filesystem = new Filesystem();
        $dirName = $this->kernel->getProjectDir().self::DIRNAME;
        $fileName = $dirName.self::FILENAME;
        try {
            $filesystem->mkdir($dirName);
        } catch (IOExceptionInterface $exception) {
            echo 'An error occurred while creating your directory at '.$exception->getPath();
        }

        if (!$io->confirm("Export file already exists ('$fileName'). Do you want to overwrite it?")) {
            $output->writeln('<error>Export cancelled!</error>');

            return;
        }

        file_put_contents($fileName, $yaml);

        $output->writeln('<info>Export completed!</info>');
    }
}
