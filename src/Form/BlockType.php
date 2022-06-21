<?php

namespace Artgris\Bundle\PageBundle\Form;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Artgris\Bundle\PageBundle\Service\TypeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    private TypeService $typeService;

    /**
     * BlockType constructor.
     */
    public function __construct(TypeService $typeService)
    {
        $this->typeService = $typeService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'form.name.placeholder',
                ],
            ])
            ->add('slug', null, [
                'attr' => [
                    'placeholder' => 'form.slug.placeholder',
                ],
                'help' => 'form.slug.help',
                'label' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => false,
                'required' => true,
                'choices' => $this->typeService->getTypes(),
            ])
            ->add('translatable', null, [
                'label' => 'form.translatable.label',
            ])
            ->add('position', HiddenType::class, [
                'attr' => [
                    'class' => 'position',
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entry = $event->getData();
            if ($entry) {
                $entry->setSlug($entry->preSlug());
            }
        });

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ArtgrisBlock::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'BlockType';
    }
}
