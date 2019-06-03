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
            new TwigFunction('regex_blok', [$this, 'getRegexBlock'], ['is_safe' => ['html']]),
            new TwigFunction('regex_array_blok', [$this, 'getRegexArrayBlock'], ['is_safe' => ['html']]),
            new TwigFunction('bloks', [$this, 'getBlocks']),
            new TwigFunction('page', [$this, 'getPage']),
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

    public function getBlock(string $bloc)
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


    public function getRegexArrayBlock(string $bloc): array
    {
        $pageBlock = $this->pageService->getBlocksByRegex($bloc);
        $value = [];
        foreach ($pageBlock as $block) {
            $value [] = $this->getBlockValue($block);
        }

        return $value;

    }

    public function getRegexBlock(string $bloc): string
    {
        return implode($this->getRegexArrayBlock($bloc));
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

    public function getPage(string $page)
    {
        $page = $this->pageService->getPageBySlug($page);
        $value = [];
        foreach ($page->getBlocks() as $block) {
            $value [] = $this->getBlockValue($block);
        }

        return $value;

    }

}
