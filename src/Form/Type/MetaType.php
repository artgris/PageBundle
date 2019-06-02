<?php

namespace Artgris\Bundle\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class MetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'form.meta.title.placeholder',
                ],
                'help' => 'form.meta.title.help',
                'constraints' => [
                    new Length(['max' => 60]),
                ],
            ])->add('description', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'form.meta.description.placeholder',
                    'rows' => '5',
                ],
                'help' => 'form.meta.description.help',
                'constraints' => [
                    new Length(['max' => 160]),
                ],
            ]);
    }
}
