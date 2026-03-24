<?php

namespace Panda\Blog\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Panda\Blog\Entity\PandaBlogPost;
use Panda\Blog\Entity\PandaBlogCategory;

/**
 * @extends ServiceEntityRepository<PandaBlogPost>
 */
class PandaBlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PandaBlogPost::class);
    }


    /** 
     * Znajdź wszystkie aktywne posty
     *
     * @return PandaBlogPost[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.is_active = :active')
            ->setParameter('active', true)
            ->orderBy('p.date_add', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź post po slug
     *
     * @param string $slug
     * @return PandaBlogPost|null
     */
    public function findBySlug(string $slug): ?PandaBlogPost
    {
        return $this->createQueryBuilder('p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Znajdź aktywny post po slug (dla frontu)
     *
     * @param string $slug
     * @return PandaBlogPost|null
     */
    public function findActiveBySlug(string $slug): ?PandaBlogPost
    {
        return $this->createQueryBuilder('p')
            ->where('p.slug = :slug')
            ->andWhere('p.is_active = :active')
            ->setParameter('slug', $slug)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Znajdź posty z daną kategorią główną
     *
     * @param PandaBlogCategory $category
     * @return PandaBlogPost[]
     */
    public function findByMainCategory(PandaBlogCategory $category): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.main_category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.date_add', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź aktywne posty z daną kategorią główną
     *
     * @param PandaBlogCategory $category
     * @return PandaBlogPost[]
     */
    public function findActiveByMainCategory(PandaBlogCategory $category): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.main_category = :category')
            ->andWhere('p.is_active = :active')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->orderBy('p.date_add', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź posty, które mają daną kategorię (główną lub dodatkową)
     *
     * @param PandaBlogCategory $category
     * @return PandaBlogPost[]
     */
    public function findByCategory(PandaBlogCategory $category): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.categories', 'c')
            ->where('p.main_category = :category')
            ->orWhere('c.id = :categoryId')
            ->setParameter('category', $category)
            ->setParameter('categoryId', $category->getId())
            ->orderBy('p.date_add', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź aktywne posty, które mają daną kategorię (główną lub dodatkową),
     * z możliwością wykluczenia wybranych postów i limitem wyników
     *
     * @param PandaBlogCategory $category
     * @param PandaBlogPost[] $exclude_posts Posty do wykluczenia z wyników
     * @param int|null $limit Limit wyników (null = wszystkie)
     * @return PandaBlogPost[]
     */
    public function findActiveByCategory(PandaBlogCategory $category, $exclude_posts = [], ?int $limit = null): array
    {   
       
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.categories', 'c')
            ->where('(p.main_category = :category OR c.id = :categoryId)')
            ->andWhere('p.is_active = :active')
            ->setParameter('category', $category)
            ->setParameter('categoryId', $category->getId())
            ->setParameter('active', true);
        
        // Wykluczenie postów, jeśli podano
        if (!empty($exclude_posts)) {
            $excludeIds = array_map(function ($post) {
                return $post->getId();
            }, $exclude_posts);

            $qb->andWhere('p.id NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        $qb->orderBy('p.date_add', 'DESC');

        // Dodaj limit, jeśli podano
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Policz posty w danej kategorii głównej
     *
     * @param PandaBlogCategory $category
     * @return int
     */
    public function countByMainCategory(PandaBlogCategory $category): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.main_category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Znajdź najnowsze posty (z limitem)
     *
     * @param int $limit
     * @return PandaBlogPost[]
     */
    public function findLatest(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.is_active = :active')
            ->setParameter('active', true)
            ->orderBy('p.date_add', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Wyszukaj posty po tytule lub treści
     *
     * @param string $query
     * @return PandaBlogPost[]
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.title LIKE :query')
            ->orWhere('p.content LIKE :query')
            ->orWhere('p.excerpt LIKE :query')
            ->andWhere('p.is_active = :active')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('active', true)
            ->orderBy('p.date_add', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Sprawdź, czy slug już istnieje (do walidacji)
     *
     * @param string $slug
     * @param int|null $excludeId ID posta do wykluczenia (przy edycji)
     * @return bool
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug);

        if ($excludeId !== null) {
            $qb->andWhere('p.id != :id')
                ->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }



    /**
     * Persist entity
     */
    public function save(PandaBlogPost $entity, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        if ($flush) {
            $em->flush();
        }
    }


    public function remove(PandaBlogPost $entity, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        if ($flush) {
            $em->flush();
        }
    }
}