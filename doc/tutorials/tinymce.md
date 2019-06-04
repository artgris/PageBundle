## How to create a TinyMCE Type


1 ) Create App\Form\TinymceType.php

    <?php
    
    namespace App\Form;
    
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    
    class TinymceType extends AbstractType
    {
    
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'attr' => [
                    'class' => 'tinymce',
                ],
                'required' => false,
            ]);
        }
    
        public function getParent()
        {
            return TextareaType::class;
        }
    
    }

2 ) add your TinyMCE js in EasyAdmin 

    easy_admin:
        design:
            assets:
                js:
                    - 'js/tinymce5/tinymce.min.js'
                    - 'js/tinymce5/plugins/media/plugin.min.js'
                    - 'js/tinymce5/plugins/image/plugin.min.js'
                    - 'js/tinymce5/init.js'
                    ...
                    
3 ) add this new type in PageBundle:

    artgris_page:
        controllers:
            - 'App\Controller\Main\'
        types:
            - TinyMCE: 'App\Form\TinymceType'
                        
                        
<img src="https://raw.githubusercontent.com/artgris/PageBundle/master/doc/images/tinymce.png" />