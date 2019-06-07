<?php

namespace Artgris\Bundle\PageBundle\Form;

use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Artgris\Bundle\PageBundle\Service\TypeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    /**
     * @var TypeService
     */
    private $typeService;

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
                'placeholder' => 'form.type.placeholder',
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

    public function getBlockPrefix()
    {
        return 'BlockType';
    }
}
