<?php

namespace Artgris\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

/**
 * @ORM\Entity()
 */
class ArtgrisBlockTranslation implements TranslationInterface
{
    use TranslationTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

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

    public function isEmpty(): bool
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
