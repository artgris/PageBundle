<?php

namespace Artgris\Bundle\PageBundle\Twig;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('artgris_lng', [$this, 'getLang']),
        ];
    }

    public function getLang(FormView $form): array
    {
        $lngList = [];
        foreach ($form->children as $child) {
            $block = $child->vars['value'];
            /** @var ArtgrisBlock $block */
            if ($block->isTranslatable()) {
                foreach ($child['translations'] as $lngs) {
                    $lng = $lngs->vars['name'];
                    if (!\in_array($lng, $lngList, true)) {
                        $lngList[] = $lng;
                    }
                }
            }
        }

        return $lngList;
    }
}
