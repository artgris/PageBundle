<?php

namespace Artgris\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity()
 */
class ArtgrisBlockTranslation
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $contentTranslatable;

    public function getContentTranslatable()
    {
        return $this->contentTranslatable;
    }

    public function setContentTranslatable($contentTranslatable): void
    {
        $this->contentTranslatable = $contentTranslatable;
    }
}
