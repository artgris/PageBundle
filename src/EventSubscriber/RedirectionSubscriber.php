<?php


namespace Artgris\Bundle\PageBundle\EventSubscriber;


use Artgris\Bundle\PageBundle\Controller\ArtgrisPageCrudController;
use Artgris\Bundle\PageBundle\Entity\ArtgrisPage;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RedirectionSubscriber implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var CrudUrlGenerator
     */
    private $crudUrlGenerator;


    /**
     * RedirectionSubscriber constructor.
     */
    public function __construct(RequestStack $requestStack, ParameterBagInterface $parameterBag, CrudUrlGenerator $crudUrlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->parameterBag = $parameterBag;
        $this->crudUrlGenerator = $crudUrlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterEntityPersistedEvent::class => ['redirectToConfiguration'],
            AfterEntityUpdatedEvent::class => ['redirectToConfiguration'],
        ];
    }

    public function redirectToConfiguration($event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $entity = $event->getEntityInstance();
        if ($request && $entity instanceof ArtgrisPage && in_array($request->get('crudAction'), ['edit', 'new'])) {

            $artgrisConfig = $this->parameterBag->get('artgrispage.config');

            if ($artgrisConfig['redirect_after_update']) {

                $url = $this->crudUrlGenerator->build()
                    ->setController(ArtgrisPageCrudController::class)
                    ->setAction("editBlocks")
                    ->setEntityId($entity->getId())
                    ->generateUrl();
                ;

                $request->query->set('referrer', $url);

            }
        }
    }
}