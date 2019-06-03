<?php

namespace Artgris\Bundle\PageBundle\Twig;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Artgris\Bundle\PageBundle\Service\PageService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BlockExtension extends AbstractExtension
{
    /**
     * @var PageService
     */
    private $pageService;

    /**
     * @var array
     */
    private $blocks;

    /**
     * BlockExtension constructor.
     */
    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('blok', [$this, 'getBlock'], ['is_safe' => ['html']]),
            new TwigFunction('bloks', [$this, 'getBlocks']),
        ];
    }

    public function getBlocks()
    {
        if (null === $this->blocks) {
            $blocks = $this->pageService->getBlocks();
            if ($blocks) {
                foreach ($blocks as $pageBlock) {
                    /** @var ArtgrisBlock $pageBlock */
                    $value = $this->getBlockValue($pageBlock);
                    $this->blocks[$pageBlock->getSlug()] = $value;
                }
            }
        }

        return $this->blocks;
    }

    public function getBlock(string $bloc): string
    {
        $block = $this->getBlocks();

        if (isset($block[$bloc])) {
            return $block[$bloc];
        }

        // block of another page
        $pageBlock = $this->pageService->getBlocksByName($bloc);

        if ($pageBlock) {
            $value = $this->getBlockValue($pageBlock);
            $this->blocks[$pageBlock->getSlug()] = $value;
            return $value ?? '';
        }

        return '';

    }

    private function getBlockValue(ArtgrisBlock $pageBlock)
    {
        $value = $pageBlock->isTranslatable() ? $pageBlock->getContentTranslatable() : $pageBlock->getContent();

        $class = $pageBlock->getType();
        $function = 'getRenderType';

        if (\class_exists($class) && \method_exists($class, $function)) {
            $value = \call_user_func([$class, $function], $value);
        }

        return $value;

    }

}
