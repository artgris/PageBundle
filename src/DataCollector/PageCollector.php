<?php

namespace Artgris\Bundle\PageBundle\DataCollector;

use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Artgris\Bundle\PageBundle\Twig\BlockExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\Yaml\Yaml;

class PageCollector extends DataCollector
{
    /**
     * @var BlockExtension
     */
    private $blockExtension;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * PageCollector constructor.
     */
    public function __construct(BlockExtension $blockExtension, EntityManagerInterface $em)
    {
        $this->blockExtension = $blockExtension;
        $this->em = $em;
    }

    /**
     * Collects data for the given Request and Response.
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $this->data['blocks_list'] = $this->blockExtension->getBlocks();
        $blocks = $this->blockExtension->getBlocksCollection();
        $this->data['blocks'] = [];
        if ($blocks) {
            foreach ($blocks as $block) {

                $this->data['blocks'][$block->getPage()->getRoute()][$block->getPage()->getName()][] = $block;
            }
        }

        krsort($this->data['blocks']);


    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     */
    public function getName()
    {
        return 'artgrispage';
    }

    public function reset()
    {
        $this->data = ['blocks' => null, 'blocks_list' => null];
    }

    public function getBlocks()
    {
        return $this->data['blocks'];
    }

    public function getBlocksList()
    {
        return $this->data['blocks_list'];
    }

    // source: easyadmin
    public function dump($variable)
    {
        if (\class_exists(HtmlDumper::class)) {
            $cloner = new VarCloner();
            $dumper = new HtmlDumper();

            $dumper->dump($cloner->cloneVar($variable), $output = \fopen('php://memory', 'rb+'));
            if (false !== $dumpedData = \stream_get_contents($output, -1, 0)) {
                return $dumpedData;
            }
        }

        if (\class_exists(Yaml::class)) {
            return \sprintf('<pre class="sf-dump">%s</pre>', Yaml::dump((array)$variable, 1024));
        }

        return \sprintf('<pre class="sf-dump">%s</pre>', \var_export($variable, true));
    }

    public function getNbBlocks()
    {
        return $this->getBlocksList() ? \count($this->getBlocksList()) : 0;
    }
}
