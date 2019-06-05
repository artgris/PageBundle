Artgris Page
============

## Installation:

    composer require artgris/page-bundle
    
    php bin/console doctrine:schema:update --force 
  
  
## Configuration:

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
 
            'text' => ArtgrisTextType::class,  => not required TextType
            'textarea' => ArtgrisTextAreaType::class,  => not required TextAreaType with rows = 8 + renderType: \nl2br

## Usage:

**1 - Create a page and add blocks**

<img src="https://raw.githubusercontent.com/artgris/PageBundle/master/doc/images/configure.png" />

**2 - Edit the content of the blocks**

<img src="https://raw.githubusercontent.com/artgris/PageBundle/master/doc/images/edit.png" />

**3 - Retrieve a simple block by tag**

<img src="https://raw.githubusercontent.com/artgris/PageBundle/master/doc/images/blok.jpg" align="right" />
  
    {{ blok('title') }}
    
    => return "My website"
    
    
use the debugging bar to easily find all blocks by route. (click on block tag to copy/paste code)

<img src="https://raw.githubusercontent.com/artgris/PageBundle/master/doc/images/debug_bar.png" />
    
**Retrieve all blocks of the current page or not linked to a page**    

    bloks()
        
ex:

    {% for blok in bloks() %}
        {{ blok }} <br>
    {% endfor %}
        
**Retrieve all blocks by page tag**    
   
    page('page-tag')
 
 ex:
        
    {% for blok in page('homepage') %}
        {{ blok }} <br>
    {% endfor %}

**Retrieve blocks with a regular expression**
    
 > in an array

    regex_array_blok('regex-expression')

ex:
  
    {% for blok in regex_array_blok('^sidebar-*') %}
        {{ blok }} <br>
    {% endfor %}
        
 > implode in a string
 
    regex_blok('regex-expression')

ex:   

    {{ regex_blok('^sidebar-*') }}  
    
    
## Tips:

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

## Tutorials

  * [How to create a TinyMCE Type](doc/tutorials/tinymce.md)


## Cache Infos

by default, all blocks of the current page or not linked to a page are loaded (and present in the debug bar),
all other calls require a call to the database:

**load by default**
- bloks()
- blok('block-linked-to-the-current-page') =>  via route selector in page configuration
- blok('block-linked-to-any-page')  => route selector left empty

**add in cache after first call**
- blok('block-of-another-page') 

**not in cache, required new db call after each call**
- page('page-tag')
- regex_array_blok('regex-expression')
- regex_blok('regex-expression')





