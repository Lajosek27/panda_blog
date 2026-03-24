<?php

namespace Panda\Blog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Panda\Blog\Repository\PandaBlogCategoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PandaBlogCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private string $slug;

    /**
     * @ORM\ManyToOne(targetEntity="PandaBlogCategory", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?PandaBlogCategory $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="PandaBlogCategory", mappedBy="parent", cascade={"persist"})
     */
    private Collection $children;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(name="meta_title", type="string", length=255, nullable=true)
     */
    private ?string $meta_title = null;

    /**
     * @ORM\Column(name="meta_description", type="string", length=512, nullable=true)
     */
    private ?string $meta_description = null;

    /**
     * @ORM\Column(name="is_active", type="boolean", options={"default": true})
     */
    private bool $is_active = true;

    /**
     * @ORM\Column(name="position", type="integer", options={"default": 0})
     */
    private int $position = 0;

    /**
     * @ORM\Column(name="date_add", type="datetime")
     */
    private \DateTimeInterface $date_add;

    /**
     * @ORM\Column(name="date_upd", type="datetime")
     */
    private \DateTimeInterface $date_upd;

    public function __construct()
    {
        // $this->children = new ArrayCollection();
        $this->date_add = new \DateTimeImmutable();
        $this->date_upd = new \DateTimeImmutable();
    }

    /**
     * @ORM\PrePersist
     */
    public function pre_persist(): void
    {
        if (!$this->date_add) {
            $this->date_add = new \DateTimeImmutable();
        }
        $this->date_upd = new \DateTimeImmutable();
    }

    /**
     * @ORM\PreUpdate
     */
    public function pre_update(): void
    {
        $this->date_upd = new \DateTimeImmutable();
    }

    // --- getters / setters (snake_case) ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getParentId(): ?Int
    {   
        return $this->parent?->id;
    }
    
    public function setParent(?PandaBlogCategory $parent): self
    {
        $this->parent = $parent;
        return $this;
    }
    
    public function getParent(): ?PandaBlogCategory
    {
        return $this->parent;
    }

    /**
     * @return Collection|PandaBlogCategory[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(PandaBlogCategory $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(PandaBlogCategory $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->meta_title;
    }

    public function setMetaTitle(?string $meta_title): self
    {
        $this->meta_title = $meta_title;
        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->meta_description;
    }

    public function setMetaDescription(?string $meta_description): self
    {
        $this->meta_description = $meta_description;
        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getDateAdd(): \DateTimeInterface
    {
        return $this->date_add;
    }

    public function getDateUpd(): \DateTimeInterface
    {
        return $this->date_upd;
    }
    
}