<?php

namespace Artgris\Bundle\PageBundle\Controller;

use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use Artgris\Bundle\PageBundle\Form\BlockConfigType;
use Artgris\Bundle\PageBundle\Form\BlockType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;

class PageController extends EasyAdminController
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * PageController constructor.
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    protected function createEntityFormBuilder($entity, $view): FormBuilder
    {
        $routeAll = [];
        $artgrisConfig = $this->getParameter('artgrispage.config');

        foreach ($this->router->getRouteCollection()->all() as $route => $params) {
            $defaults = $params->getDefaults();
            if (\array_key_exists('_controller', $defaults)) {
                $controller = $defaults['_controller'];
                foreach ($artgrisConfig['controllers'] as $controllerName) {
                    if (false !== \strpos($defaults['_controller'], $controllerName)) {
                        if (!\in_array($controller, $routeAll, true)) {
                            $routeAll[$params->getPath()] = $controller;
                        }
                    }
                }
            }
        }

        $builder = parent::createEntityFormBuilder($entity, $view);
        $builder
            ->add('name', null, [
                'label' => 'form.name.label',
            ])
            ->add('route', ChoiceType::class,
                [
                    'label' => 'form.route.label',
                    'choices' => $routeAll,
                    'required' => false,
                ]
            )
            ->add('blocks', CollectionType::class, [
                'required' => false,
                'entry_type' => BlockType::class,
                'by_reference' => false,
                'attr' => [
                    'class' => 'artgris-page-collection',
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_options' => [
                    'label' => false,
                ],
                'label' => 'form.blocks.label',
            ]);

        if ($artgrisConfig['hide_route_form']) {
            $builder->remove('route');
        }

        return $builder;
    }

    public function editBlocksAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository(ArtgrisPage::class)->find($id);

        $formBuilder = parent::createEntityFormBuilder($entity, 'edit')
            ->remove('route')
            ->remove('name')
            ->add('blocks', CollectionType::class, [
                'entry_type' => BlockConfigType::class,
            ]);

        try {
            $form = $formBuilder->getForm();
        } catch (UnexpectedTypeException $e) {
            $this->addFlash('danger', 'unexpected_type');

            return $this->redirectToRoute('easyadmin', [
                'action' => 'edit',
                'entity' => $this->entity['name'],
                'menuIndex' => $this->request->query->get('menuIndex'),
                'submenuIndex' => $this->request->query->get('submenuIndex'),
                'id' => $this->request->query->get('id'),
            ]);
        }

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToReferrer();
        }
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);

        if ($entity->getBlocks()->isEmpty()) {
            $this->addFlash('warning', 'blocks_empty');
        }

        return $this->render('@ArtgrisPage/edit_blocks.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    protected function renderTemplate($actionName, $templatePath, array $parameters = [])
    {
        if ('list' === $actionName) {
            $artgrisConfig = $this->getParameter('artgrispage.config');
            if ($artgrisConfig['hide_route_form']) {
                unset($parameters['fields']['route']);
            }
        }

        return $this->render($templatePath, $parameters);
    }

    protected function redirectToReferrer()
    {
        $refererAction = $this->request->query->get('action');

        $artgrisConfig = $this->getParameter('artgrispage.config');

        if ($artgrisConfig['redirect_after_update']) {
            if (\in_array($refererAction, ['new', 'edit']) && $this->isActionAllowed('edit')) {
                return $this->redirectToRoute('easyadmin', [
                    'action' => 'editBlocks',
                    'entity' => $this->entity['name'],
                    'menuIndex' => $this->request->query->get('menuIndex'),
                    'submenuIndex' => $this->request->query->get('submenuIndex'),
                    'id' => ('new' === $refererAction)
                        ? PropertyAccess::createPropertyAccessor()->getValue($this->request->attributes->get('easyadmin')['item'], $this->entity['primary_key_field_name'])
                        : $this->request->query->get('id'),
                ]);
            }

        }

        return parent::redirectToReferrer();
    }
}
