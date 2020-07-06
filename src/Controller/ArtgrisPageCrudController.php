<?php


namespace Artgris\Bundle\PageBundle\Controller;


use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Artgris\Bundle\PageBundle\Form\BlockType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Routing\RouterInterface;

class ArtgrisPageCrudController extends AbstractCrudController
{
    /**
     * @var RouterInterface
     */
    private $router;


    /**
     * ArtgrisPageCrudController constructor.
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getEntityFqcn(): string
    {
        return ArtgrisPage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Produits')
            ->setPageTitle(Crud::PAGE_INDEX, 'artgrispage.list.title')
            ->setDefaultSort(['name' => 'ASC'])
            ->setFormThemes([
                "@EasyAdmin/crud/form_theme.html.twig",
                '@ArtgrisPage/themes/form.html.twig'

            ])
            ->overrideTemplates([
                'crud/edit' => "@ArtgrisPage/edit.html.twig",
                'crud/new' => "@ArtgrisPage/new.html.twig"
            ])
            ->setPageTitle('index', 'artgrispage.list.title')
            ->setPageTitle('new', 'artgrispage.action.new')
            ->setPageTitle('edit', 'artgrispage.action.edit');
    }

    public function configureActions(Actions $actions): Actions
    {
//        $editBlocks = Action::new('editBlocks', null, 'fa fa-pencil')->linkToRoute("ed")
        return $actions
            ->disable(Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel("artgrispage.action.new");
            });
    }

    public function configureFields(string $pageName): iterable
    {

        $routeAll = [];
        $artgrisConfig = $this->getParameter('artgrispage.config');
        foreach ($this->router->getRouteCollection()->all() as $route => $params) {
            $defaults = $params->getDefaults();
            if (\array_key_exists('_controller', $defaults)) {
                $controller = $defaults['_controller'];
                foreach ($artgrisConfig['controllers'] as $controllerName) {
                    if (false !== \mb_strpos($defaults['_controller'], $controllerName)) {
                        if (!\in_array($controller, $routeAll, true)) {
                            $routeAll[$params->getPath()] = $controller;
                        }
                    }
                }
            }
        }


        $fieldName = 'name';
        $fieldSlug = TextField::new('slug')
            ->setLabel('form.slug.placeholder')
            ->setHelp('form.slug.help')
            ->setRequired(false);
        $fieldRoute = ChoiceField::new('route')
            ->setChoices($routeAll)
            ->setLabel('form.route.label')
            ->setRequired(false);
        $fieldBlocks = CollectionField::new('blocks')
            ->setRequired(false)
            ->setEntryType(BlockType::class)
            ->setFormTypeOptions([
                    "by_reference" => false,
                    'attr' => [
                        'class' => 'artgris-page-collection',
                    ],
                    'delete_empty' => true,
                    'entry_options' => [
                        'label' => false,
                    ],
                ]
            )
            ->allowAdd()
            ->allowDelete()
            ->setLabel("form.blocks.label")
            ->addJsFiles('bundles/artgrispage/js/jquery.collection.js');


        if ($artgrisConfig['hide_route_form']) {
            return [
                $fieldName,
                $fieldSlug,
                $fieldBlocks
            ];
        }

        return [
            $fieldName,
            $fieldSlug,
            $fieldRoute,
            $fieldBlocks
        ];


    }

}