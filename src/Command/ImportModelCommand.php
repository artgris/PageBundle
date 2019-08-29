<?php

namespace Artgris\Bundle\PageBundle\Command;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ImportPageCommand.
 */
class ImportModelCommand extends Command
{
    private const REMOVE_DEVIANTS = 'remove-deviants';
    protected static $defaultName = 'artgris:page:import';
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

    protected function configure()
    {
        $this
            ->addOption(self::REMOVE_DEVIANTS, null, InputOption::VALUE_NONE, 'Delete the content of types that have changed');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $pageRepository = $this->em->getRepository(ArtgrisPage::class);
        $blockRepository = $this->em->getRepository(ArtgrisBlock::class);

        $fileName = $this->kernel->getProjectDir() . ExportModelCommand::DIRNAME . ExportModelCommand::FILENAME;
        $pages = Yaml::parseFile($fileName);

        $io->title('Operations found:');

        $operations = [];

        foreach ($pages as $pageSlug => $page) {
            $originalPageEntity = null;
            $pageEntity = $pageRepository->findOneBy(['slug' => $pageSlug]);
            if ($pageEntity === null) {
                $pageEntity = new ArtgrisPage();
                $pageEntity->setSlug($pageSlug);
                $operations[] = "<fg=default;bg=green>Create page '{$pageSlug}'</>";
            } else {
                $originalPageEntity = clone $pageEntity;
            }
            $pageEntity->setRoute($page['route']);
            $pageEntity->setName($page['name']);

            if ($originalPageEntity !== null && !empty($fields = $this->comparePages($originalPageEntity, $pageEntity))) {
                $operations[] = "<fg=default;bg=yellow>Edit page '" . $pageSlug . "' (" . implode(',', $fields) . ')</>';
            }

            $this->em->persist($pageEntity);

            $position = 0;
            foreach ($page['blocks'] as $blockSlug => $block) {
                $originalBlockEntity = null;
                $blockEntity = $blockRepository->findOneBy(['slug' => $blockSlug]);
                if ($blockEntity === null) {
                    $blockEntity = new ArtgrisBlock();
                    $blockEntity->setSlug($blockSlug);
                    $operations[] = "<fg=default;bg=green>Create block '{$blockSlug}'</>";
                } else {
                    $originalBlockEntity = clone $blockEntity;
                }

                $blockEntity->setPage($pageEntity);
                $blockEntity->setPosition($position);
                $blockEntity->setName($block['name']);
                $blockEntity->setTranslatable($block['translatable']);
                $blockEntity->setType($block['type']);


                if ($originalBlockEntity !== null && !empty($fields = $this->compareBlocks($originalBlockEntity, $blockEntity))) {
                    $operations[] = "<fg=default;bg=yellow>Edit block '" . $blockSlug . "' (" . implode(',', $fields) . ')</>';

                    if ($blockEntity->getType() !== $originalBlockEntity->getType()) {

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
                }

                $this->em->persist($blockEntity);
                $position++;
            }
        }

        if (!empty($operations)) {

            $io->listing($operations);

            if (!$io->confirm('Executes the queries(flush) ?')) {
                $io->writeln('<error>Import cancelled!</error>');

                return;
            }

            // confirmation before flush
            $this->em->flush();
            $io->writeln('<info> Import completed!</info>');
        } else {
            $io->success('Nothing to do.');
        }
    }

    private function comparePages(ArtgrisPage $origin, ArtgrisPage $update)
    {
        $metaData = $this->em->getClassMetadata(ArtgrisPage::class);

        return $this->compareEntity($origin, $update, $metaData, ['name', 'route']);
    }

    private function compareBlocks(ArtgrisBlock $origin, ArtgrisBlock $update)
    {
        $metaData = $this->em->getClassMetadata(ArtgrisBlock::class);

        return $this->compareEntity($origin, $update, $metaData, ['name', 'position', 'translatable', 'type']);
    }

    private function compareEntity($origin, $update, ClassMetadataInfo $metaData, array $fields)
    {
        $fieldsUpdate = [];

        foreach ($fields as $field) {
            $valueOrigin = $metaData->getFieldValue($origin, $field);
            $valueUpload = $metaData->getFieldValue($update, $field);

            if ($valueOrigin !== $valueUpload) {
                $fieldsUpdate[] = $field;
            }
        }

        return $fieldsUpdate;
    }
}
