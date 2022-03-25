<?php

namespace Artgris\Bundle\PageBundle\Entity;

use Artgris\Bundle\PageBundle\Repository\ArtgrisBlockRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\RelativeSlugHandler;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @method getContentTranslatable()
 */
#[ORM\Entity(repositoryClass: ArtgrisBlockRepository::class)]
class ArtgrisBlock implements TranslatableInterface
{
    use TranslatableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private ?int $position = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    protected ?string $type = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private mixed $content = null;

    #[ORM\ManyToOne(targetEntity: ArtgrisPage::class, inversedBy: 'blocks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\SortableGroup()]
    private ?ArtgrisPage $page = null;

    #[ORM\Column(type: 'string', length: 128, unique:true)]
    #[Gedmo\Slug(fields: ['name'], updatable: false)]
    #[Gedmo\SlugHandler(class: RelativeSlugHandler::class, options: [
        'relationField' => 'page',
        'relationSlugField' => 'slug',
        'separator' => '-',
    ])]
    private string $slug;

    #[ORM\Column(type: 'boolean')]
    private ?bool $translatable = false;

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function setContent(mixed $content): void
    {
        $this->content = $content;
    }

    public function getPage(): ?ArtgrisPage
    {
        return $this->page;
    }

    public function setPage(?ArtgrisPage $page): void
    {
        $this->page = $page;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function isTranslatable(): ?bool
    {
        return $this->translatable;
    }

    public function setTranslatable(?bool $translatable): void
    {
        $this->translatable = $translatable;
    }

    public function __toString(): string
    {
        return $this->slug;
    }
}
