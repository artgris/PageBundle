<?php

namespace Artgris\Bundle\PageBundle\Entity;

use Artgris\Bundle\PageBundle\Repository\ArtgrisPageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArtgrisPageRepository::class)]
class ArtgrisPage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $route = null;

    /**
     * @var Collection<ArtgrisBlock>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'page', targetEntity: ArtgrisBlock::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $blocks;

    #[ORM\Column(type: 'string', length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['name'], updatable: false)]
    private ?string $slug = null;

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

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): void
    {
        $this->route = $route;
    }

    public function getBlocks(): ArrayCollection|Collection
    {
        return $this->blocks;
    }

    public function setBlocks(ArrayCollection|Collection $blocks): void
    {
        $this->blocks = $blocks;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
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

    public function __toString()
    {
        return $this->getSlug();
    }
}
