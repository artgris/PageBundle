<?php

namespace Artgris\Bundle\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtgrisTextAreaType extends AbstractType implements PageFromInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['rows' => '8'],
            'required' => false,
        ]);
    }

    public static function getRenderType($value): string
    {
        return nl2br($value);
    }

    public function getParent(): ?string
    {
        return TextareaType::class;
    }
}
