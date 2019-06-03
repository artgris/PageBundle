<?php

namespace Artgris\Bundle\PageBundle\Service;

use Artgris\Bundle\PageBundle\Form\Type\ArtgrisTextAreaType;
use Artgris\Bundle\PageBundle\Form\Type\ArtgrisTextType;
use Artgris\Bundle\PageBundle\Form\Type\MetaType;
use Artgris\Bundle\PageBundle\Form\Type\TinymceType;

class TypeService
{
    /**
     * @var array
     */
    private $config;

    /**
     * TypeService constructor.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getTypes()
    {
        $typesList = $this->config['default_types'] ? [
            'type.text' => ArtgrisTextType::class,
            'type.textarea' => ArtgrisTextAreaType::class,
            'type.tiny_mce' => TinymceType::class,
            'type.meta' => MetaType::class,
        ] : [];

        foreach ($this->config['types'] as $types) {
            foreach ($types as $key => $value) {
                $typesList[$key] = $value;
            }
        }

        return $typesList;
    }
}
