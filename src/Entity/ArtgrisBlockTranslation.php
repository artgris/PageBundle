<?php

namespace Artgris\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[ORM\Entity()]
class ArtgrisBlockTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private mixed $contentTranslatable;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContentTranslatable(): mixed
    {
        return $this->contentTranslatable;
    }

    public function setContentTranslatable(mixed $contentTranslatable)
    {
        $this->contentTranslatable = $contentTranslatable;
    }

    public function isEmpty(): bool
    {
        foreach (get_object_vars($this) as $var => $value) {
            if (\in_array($var, ['id', 'translatable', 'locale'])) {
                continue;
            }

            if (!empty($value)) {
                if (\is_array($value)) {
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
