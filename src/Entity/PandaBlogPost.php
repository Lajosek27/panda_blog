<?php

namespace Panda\Blog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Panda\Blog\Repository\PandaBlogPostRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PandaBlogPost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private ?int $id = null;

    /** @ORM\Column(type="string", length=255) */
    private string $title;

    /** @ORM\Column(type="string", length=255) */
    private string $slug;

    /**
     * @ORM\ManyToOne(targetEntity="PandaBlogCategory")
     * @ORM\JoinColumn(name="main_category_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?PandaBlogCategory $main_category = null;

    /**
     * @ORM\ManyToMany(targetEntity="PandaBlogCategory")
     * @ORM\JoinTable(
     *     name="fs_panda_blog_post_category",
     *     joinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private Collection $categories;

    /** @ORM\Column(name="excerpt", type="text", nullable=true) */
    private ?string $excerpt = null;

    /** @ORM\Column(name="content", type="text", nullable=true) */
    private ?string $content = null;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private ?string $image = null;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private ?string $meta_title = null;

    /** @ORM\Column(type="string", length=512, nullable=true) */
    private ?string $meta_description = null;

    /** @ORM\Column(type="string", length=255) */
    private ?string $author;

    /** @ORM\Column(type="boolean", options={"default": true}) */
    private bool $is_active = true;

    /**
     * @ORM\Column(type="json")
     */
    private array $related_product_ids = [];

    /** @ORM\Column(type="datetime") */
    private \DateTimeInterface $date_add;

    /** @ORM\Column(type="datetime") */
    private \DateTimeInterface $date_upd;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->related_product_ids = [];
        $this->date_add = new \DateTime();
        $this->date_upd = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        $this->date_upd = new \DateTime();
        if ($this->date_add === null) {
            $this->date_add = new \DateTime();
        }
    }

    // ==================== GETTERY I SETTERY ====================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
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

    public function getMainCategory(): ?PandaBlogCategory
    {
        return $this->main_category;
    }

    public function setMainCategory(?PandaBlogCategory $main_category): self
    {
        $this->main_category = $main_category;
        return $this;
    }

    /**
     * @return Collection<int, PandaBlogCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(PandaBlogCategory $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
        return $this;
    }

    public function removeCategory(PandaBlogCategory $category): self
    {
        $this->categories->removeElement($category);
        return $this;
    }
    public function getRelatedProductIds(): array
    {
        return $this->related_product_ids; 
    }
   
    public function setRelatedProductIds(array $ids): self
    {
        $this->related_product_ids = $ids;
        return $this;
    }
    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): self
    {
        $this->excerpt = $excerpt;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;
        return $this;
    }
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
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

    public function getDateAdd(): \DateTimeInterface
    {
        return $this->date_add;
    }

    public function setDateAdd(\DateTimeInterface $date_add): self
    {
        $this->date_add = $date_add;
        return $this;
    }

    public function getDateUpd(): \DateTimeInterface
    {
        return $this->date_upd;
    }

    public function setDateUpd(\DateTimeInterface $date_upd): self
    {
        $this->date_upd = $date_upd;
        return $this;
    }


}