<?php

namespace Artgris\Bundle\PageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Artgris\Bundle\PageBundle\Repository\ArtgrisPageRepository")
 */
class ArtgrisPage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $route;

    /**
     * @Assert\Valid()
     * @ORM\OneToMany(targetEntity="Artgris\Bundle\PageBundle\Entity\ArtgrisBlock", mappedBy="page", orphanRemoval=true, cascade={"persist"})
     */
    private $blocks;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Collection|ArtgrisBlock[]
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function addBlock(ArtgrisBlock $block): self
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks[] = $block;
            $block->setPage($this);
        }

        return $this;
    }

    public function removeBlock(ArtgrisBlock $block): self
    {
        if ($this->blocks->contains($block)) {
            $this->blocks->removeElement($block);
            if ($block->getPage() === $this) {
                $block->setPage(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * @param string $route
     */
    public function setRoute(?string $route): void
    {
        $this->route = $route;
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
