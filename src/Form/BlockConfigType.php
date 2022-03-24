<?php

namespace Artgris\Bundle\PageBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Artgris\Bundle\PageBundle\Entity\ArtgrisBlock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $child = $event->getData();

                if ($child instanceof ArtgrisBlock) {
                    $helpTag = '<i class="fa fa-tag"></i> '.$child->getSlug();

                    $typeExists = class_exists($child->getType());
                    $content = [
                        'label' => $child->getName(),
                        'help' => $typeExists ? $helpTag : "{$helpTag} (Type {$child->getType()} not found.)",
                    ];

                    if (\in_array($child->getType(), [
                        DateType::class,
                        DateIntervalType::class,
                        DateTimeType::class,
                        TimeType::class,
                        BirthdayType::class,
                    ], true)) {
                        $content['input'] = 'string';
                    }

                    if ($child->isTranslatable()) {
                        $content['field_type'] = $typeExists ? $child->getType() : null;
                        $form->add(
                            'translations',
                            TranslationsType::class,
                            [
                                'fields' => [
                                    'contentTranslatable' => $content,
                                ],
                            ]
                        );
                    } else {
                        $form->add('content', $child->getType(), $content);
                    }
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ArtgrisBlock::class,
            'label' => false,
        ]);
    }
}
