<?php

namespace Artgris\Bundle\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'block_prefix' => 'art_section',
            'form_type' =>'section'
        ]);
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }
}
