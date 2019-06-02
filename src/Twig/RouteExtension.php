<?php

namespace Artgris\Bundle\PageBundle\Twig;

use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RouteExtension extends AbstractExtension
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * RouteExtension constructor.
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('route_name', [$this, 'transformeToRoute']),
        ];
    }

    public function transformeToRoute(?string $routeName): string
    {
        if (null === $routeName) {
            return '';
        }

        foreach ($this->router->getRouteCollection()->all() as $params) {
            $defaults = $params->getDefaults();
            $controller = $defaults['_controller'];
            if ($routeName === $controller) {
                return $params->getPath();
            }
        }

        return '';
    }
}
