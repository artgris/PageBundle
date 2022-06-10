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
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class ArtgrisPageCrudController extends AbstractCrudController
{
    /**
     * ArtgrisPageCrudController constructor.
     */
    public function __construct(private RouterInterface $router, private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ArtgrisPage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $artgrisConfig = $this->getParameter('artgrispage.config');

        $crud
            ->setEntityLabelInPlural('Produits')
            ->setPageTitle(Crud::PAGE_INDEX, 'artgrispage.list.title')
            ->setDefaultSort(['name' => 'ASC'])
            ->addFormTheme('@ArtgrisPage/themes/form.html.twig')
            ->addFormTheme('@ArtgrisPage/themes/edit_blocks.html.twig')
            ->overrideTemplates([
                'crud/edit' => '@ArtgrisPage/edit.html.twig',
                'crud/new' => '@ArtgrisPage/new.html.twig',
            ])
            ->setPageTitle('index', 'artgrispage.list.title')
            ->setPageTitle('new', 'artgrispage.action.new')
            ->setPageTitle('edit', 'artgrispage.action.edit');

        if ($artgrisConfig['use_multiple_a2lix_form']) {
            $crud->addFormTheme('@ArtgrisPage/themes/a2lix/multiple_form.html.twig');
         } else {
            $crud->addFormTheme('@ArtgrisPage/themes/a2lix/form.html.twig');
        }

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        $editBlocksAction = Action::new('editBlocks', 'artgrispage.action.config', null)
            ->linkToCrudAction('editBlocks');

        return $actions
            ->disable(Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action): Action => $action->setLabel('artgrispage.action.new'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $action): Action => $action->setLabel('artgrispage.action.edit'))
            ->add(Crud::PAGE_INDEX, $editBlocksAction);
    }

    /**
     * @throws Exception
     */
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

        if (!$routeAll) {
            throw new Exception("Your artgris_page.controllers configuration doesn't return routes");
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
            ->setSortable(false)
            ->setFormTypeOptions(
                [
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
            ->setTemplatePath('@ArtgrisPage/fields/collection.html.twig')
            ->addJsFiles('bundles/artgrispage/js/jquery-3.6.0.min.js')
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

    public function editBlocks(AdminContext $context): KeyValueStore|RedirectResponse
    {
        $event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::EDIT, 'entity' => null])) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        $this->container->get(EntityFactory::class)->processFields(
            $context->getEntity(),
            FieldCollection::new($this->configureFields(Crud::PAGE_EDIT))
        );
        $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
        $entityInstance = $context->getEntity()->getInstance();

        $parameters = KeyValueStore::new([
            'attr' => ['class' => 'config-form ea-edit-form'],
        ]);
        $editForm = $this->createEditForm($context->getEntity(), $parameters, $context);
        $editForm
            ->remove('route')
            ->remove('name')
            ->remove('slug')
            ->add('blocks', CollectionType::class, [
                'entry_type' => BlockConfigType::class,
                'block_prefix' => 'art_block',
            ]);

        $editForm->handleRequest($context->getRequest());

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $event = new BeforeEntityUpdatedEvent($entityInstance);
            $this->container->get('event_dispatcher')->dispatch($event);
            $entityInstance = $event->getEntityInstance();

            $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

            $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));
            $submitButtonName = $context->getRequest()->get('ea')['newForm']['btn'];
            if (Action::SAVE_AND_CONTINUE === $submitButtonName) {
                $url = $this->adminUrlGenerator
                    ->setAction('editBlocks')
                    ->setEntityId($context->getEntity()->getPrimaryKeyValue())
                    ->generateUrl();

                return $this->redirect($url);
            }

            if (Action::SAVE_AND_RETURN === $submitButtonName) {
                $url = $context->getReferrer()
                    ?? $this->adminUrlGenerator->setAction(Action::INDEX)->generateUrl();

                return $this->redirect($url);
            }

            return $this->redirectToRoute($context->getDashboardRouteName());
        }

        return $this->configureResponseParameters(
            KeyValueStore::new([
                'pageName' => Crud::PAGE_EDIT,
                'templateName' => 'crud/edit',
                'edit_form' => $editForm,
                'entity' => $context->getEntity(),
            ])
        );
    }
}
