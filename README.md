Artgris Page
============

<img src="https://raw.githubusercontent.com/artgris/PageBundle/master/doc/images/configure.png" align="right" />

## Installation:

    composer require artgris/page-bundle
    
    php bin/console doctrine:schema:update --force 
  
  
## Configuration

in config/packages
     
### add easy_admin.yaml: 
   
     imports:
        - { resource: '@ArtgrisPageBundle/Resources/config/easy_admin.yaml' }
    
    
if you have a custom menu add ArtgrisPage entry:

    easy_admin:
      design:
          menu:
              - {entity: ArtgrisPage, label: Pages }

### add a2lix_translation_form.yaml

ex:
           
    a2lix_translation_form:
        locale_provider: default
        locales: [fr, en]
        default_locale: fr
        
### add artgris_page.yaml 

not required, no minimal configuration
    
    artgris_page:
        controllers: #Namespaces used to load the route selector
            - 'App\Controller\MainController::index'
            - 'App\Controller\Main\'
            - 'App\Controller\'
            - ... 
        types: # add your own types
            -   integer: 'Symfony\Component\Form\Extension\Core\Type\IntegerType'
            -   date: 'Symfony\Component\Form\Extension\Core\Type\DateType'
            -   time: 'Symfony\Component\Form\Extension\Core\Type\TimeType'
            -   custom: 'App\Form\CustomType'
            - ... 
        default_types: true #load default form types [1]
        hide_route_form: false #to hide the route selector (example of use: one page website)
        redirect_after_update: false #always redirect the user to the configuration page after new/edit action
        
[1] Default form types list:

    source: Artgris\Bundle\PageBundle\Service\TypeService
 
            'type.text' => TextType::class,
            'type.textarea' => ArtgrisTextAreaType::class,
            'type.tiny_mce' => TinymceType::class,
            'type.meta' => MetaType::class, (title + description)

## Usage

**Retrieve a simple block by tag**

    {{ blok('blockTag') }}
    
use the debugging bar to easily find all blocks by route. (click on block tag to copy/paste code)
    
**Retrieve all blocks by page tag**    
   
    page('pageTag')
 
 ex:
        
    {% for blok in page('homepage') %}
        {{ blok }} <br>
    {% endfor %}

**Retrieve blocks with a regular expression**
    
 > in an array

    regex_array_blok('REGEX_EXPRESSION')

ex:
  
    {% for blok in regex_array_blok('^sidebar-*') %}
        {{ blok }} <br>
    {% endfor %}
        
 > implode in a string
 
    regex_blok('REGEX_EXPRESSION')

ex:   

    {{ regex_blok('^sidebar-*') }}  
    
    
## Tips

### custom block rendering

create a form type that implements PageFromInterface:

    namespace App\Form;
    
    use Artgris\Bundle\PageBundle\Form\Type\PageFromInterface;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    
    class CustomType extends AbstractType implements PageFromInterface
    {
    
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'attr' => [
                    'class' => 'custom',
                ],
                'required' => false,
            ]);
        }
    
        public function getParent()
        {
            return TextareaType::class;
        }
    
    
        public static function getRenderType($value)
        {
            return $value. '<hr>';
        }
    }


Edit the rendering as you wish using the getRenderType method.
