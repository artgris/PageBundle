<?php

namespace Artgris\Bundle\PageBundle\DataCollector;

use Artgris\Bundle\PageBundle\Twig\BlockExtension;
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
     * PageCollector constructor.
     */
    public function __construct(BlockExtension $blockExtension)
    {
        $this->blockExtension = $blockExtension;
    }

    /**
     * Collects data for the given Request and Response.
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['blocks'] = $this->blockExtension->getBlocks();
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
        $this->data = ['blocks' => null];
    }

    public function getBlocks()
    {
        return $this->data['blocks'];
    }

    // source: easyadmin
    public function dump($variable)
    {
        if (\class_exists(HtmlDumper::class)) {
            $cloner = new VarCloner();
            $dumper = new HtmlDumper();

            $dumper->dump($cloner->cloneVar($variable), $output = \fopen('php://memory', 'r+b'));
            if (false !== $dumpedData = \stream_get_contents($output, -1, 0)) {
                return $dumpedData;
            }
        }

        if (\class_exists(Yaml::class)) {
            return \sprintf('<pre class="sf-dump">%s</pre>', Yaml::dump((array) $variable, 1024));
        }

        return \sprintf('<pre class="sf-dump">%s</pre>', \var_export($variable, true));
    }

    public function getNbBlocks()
    {
        return $this->getBlocks() ? \count($this->getBlocks()) : 0;
    }
}
