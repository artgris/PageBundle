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

    public function isEmpty()
    {

        foreach (get_object_vars($this) as $var => $value) {
            if (in_array($var, ['id', 'translatable', 'locale'])) {
                continue;
            }

            if (!empty($value)) {
                if (is_array($value)) {
                    if (!empty(array_filter($value))) {
                    return false;
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }
    
}
