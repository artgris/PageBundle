<?php

namespace Artgris\Bundle\PageBundle\Controller;

use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Artgris\Bundle\PageBundle\Form\BlockConfigType;
use Artgris\Bundle\PageBundle\Form\BlockType;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
                '@EasyAdmin/crud/form_theme.html.twig',
                '@ArtgrisPage/themes/form.html.twig',
                '@ArtgrisPage/themes/edit_blocks.html.twig'
            ])
            ->overrideTemplates([
                'crud/edit' => '@ArtgrisPage/edit.html.twig',
                'crud/new' => '@ArtgrisPage/new.html.twig',
            ])
            ->setPageTitle('index', 'artgrispage.list.title')
            ->setPageTitle('new', 'artgrispage.action.new')
            ->setPageTitle('edit', 'artgrispage.action.edit');
    }

    public function configureActions(Actions $actions): Actions
    {
        $editBlocksAction = Action::new('editBlocks', false, 'fa fa-pencil')->linkToCrudAction('editBlocks');
        $editBlocksAction->getAsDto()->setRouteParameters(['lol' => 'olo']);

        return $actions
            ->disable(Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('artgrispage.action.new');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel(false)->setIcon('fa fa-cog');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel(false)->setIcon('fa fa-trash');
            })
            ->add(Crud::PAGE_INDEX, $editBlocksAction);
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
                    if (false !== mb_strpos($defaults['_controller'], $controllerName)) {
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
                    'by_reference' => false,
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
            ->setLabel('form.blocks.label')
            ->addJsFiles('bundles/artgrispage/js/jquery.collection.js')
            ->addCssFiles('bundles/artgrispage/style.css');

        if ($artgrisConfig['hide_route_form']) {
            return [
                $fieldName,
                $fieldSlug,
                $fieldBlocks,
            ];
        }

        return [
            $fieldName,
            $fieldSlug,
            $fieldRoute,
            $fieldBlocks,
        ];
    }

    public function editBlocks(AdminContext $context)
    {
//
//        $context->getCrud()->setFormThemes([
//            '@EasyAdmin/crud/form_theme.html.twig',
//            '@ArtgrisPage/themes/form.html.twig',
//            '@ArtgrisPage/themes/edit_blocks.html.twig'
//        ]);
//
        $this->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_EDIT)));
//        $this->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());
        $entityInstance = $context->getEntity()->getInstance();

        $parameters = KeyValueStore::new([
            'attr' => ['class' => 'config-form'],
        ]);

        $editForm = $this->createEditForm($context->getEntity(), $parameters, $context);

        $editForm
            ->remove('route')
            ->remove('name')
            ->remove('slug')
            ->add('blocks', CollectionType::class, [
                'entry_type' => BlockConfigType::class,
                'block_prefix' => 'art_block'
            ]);

        $editForm->handleRequest($context->getRequest());
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // TODO:
            // $this->processUploadedFiles($editForm);

            $event = new BeforeEntityUpdatedEvent($entityInstance);
            $this->get('event_dispatcher')->dispatch($event);
            $entityInstance = $event->getEntityInstance();

            $this->updateEntity($this->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

            $this->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

            $submitButtonName = $context->getRequest()->request->get('ea')['newForm']['btn'];
            if (Action::SAVE_AND_CONTINUE === $submitButtonName) {
                $url = $this->get(CrudUrlGenerator::class)->build()
                    ->setAction(Action::EDIT)
                    ->setEntityId($context->getEntity()->getPrimaryKeyValue())
                    ->generateUrl();

                return $this->redirect($url);
            }

            if (Action::SAVE_AND_RETURN === $submitButtonName) {
                $url = $context->getReferrer()
                    ?? $this->get(CrudUrlGenerator::class)->build()->setAction(Action::INDEX)->generateUrl();

                return $this->redirect($url);
            }

            return $this->redirectToRoute($context->getDashboardRouteName());
        }


        return $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_EDIT,
            'templateName' => 'crud/edit',
            'edit_form' => $editForm,
            'entity' => $context->getEntity(),
        ]));
    }
}
