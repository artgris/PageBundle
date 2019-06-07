<?php

namespace Artgris\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Artgris\Bundle\PageBundle\Repository\ArtgrisBlockRepository")
 *
 * @method getContentTranslatable()
 */
class ArtgrisBlock
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank();
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank();
     */
    protected $type;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="ArtgrisPage", inversedBy="blocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $page;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $translatable = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPage(): ?ArtgrisPage
    {
        return $this->page;
    }

    public function setPage(?ArtgrisPage $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }

    public function isTranslatable(): ?bool
    {
        return $this->translatable;
    }

    public function setTranslatable(?bool $translatable): void
    {
        $this->translatable = $translatable;
    }

    /**
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
