<?php

namespace Artgris\Bundle\PageBundle\Command;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class RemoveExtraCommand extends Command
{
    protected static $defaultName = 'artgris:page:remove:extra';
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

        $fileName = $this->kernel->getProjectDir() . ExportModelCommand::DIRNAME . ExportModelCommand::FILENAME;
        $pages = Yaml::parseFile($fileName);

        $extraPages = $this->em->getRepository(ArtgrisPage::class)->findPageDiff(array_keys($pages));

        if (\count($extraPages) > 0) {
            $io->title('Extra pages Found:');

            $io->listing($extraPages);

            if (!$io->confirm('Are you sure you want to delete these pages?')) {
                $io->writeln('<error>Delete cancelled!</error>');

                return;
            }

            foreach ($extraPages as $extraPage) {
                $this->em->remove($extraPage);
            }

            $this->em->flush();

            $io->success('Pages deleted');
        } else {
            $io->success('No page to delete');
        }


        $blocks = [];
        foreach ($pages as $page) {
            foreach ($page['blocks'] as $blockSlug => $block) {
                $blocks[] = $blockSlug;
            }
        }
        $extraBlocks = $this->em->getRepository(ArtgrisBlock::class)->findBlockDiff($blocks);

        if (\count($extraBlocks) > 0) {
            $io->title('Extra blocks Found:');

            $io->listing($extraBlocks);

            if (!$io->confirm('Are you sure you want to delete these blocks?')) {
                $io->writeln('<error>Delete cancelled!</error>');

                return;
            }

            foreach ($extraBlocks as $extraBlock) {
                $this->em->remove($extraBlock);
            }

            $this->em->flush();

            $io->success('Blocks deleted');
        } else {
            $io->success('No block to delete');
        }

    }
}
