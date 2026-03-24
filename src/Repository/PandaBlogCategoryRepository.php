<?php

namespace Panda\Blog\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Panda\Blog\Entity\PandaBlogCategory;

/**
 * @extends ServiceEntityRepository<PandaBlogCategory>
 */
class PandaBlogCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PandaBlogCategory::class);
    }

    /**
     * Persist entity
     */
    public function save(PandaBlogCategory $entity, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Remove entity
     */
    public function remove(PandaBlogCategory $entity, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Find single category by id
     */
    public function findById(int $id): ?PandaBlogCategory
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    /**
     * Find single category by slug
     */
    public function findBySlug(string $slug): ?PandaBlogCategory
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find root categories (parent_id IS NULL), ordered by position
     *
     * @return PandaBlogCategory[]
     */
    public function findRoots(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parent_id IS NULL')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find direct children of given category, ordered by position
     *
     * @return PandaBlogCategory[]
     */
    public function findChildren(PandaBlogCategory $parent): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parent_id = :parent')
            ->setParameter('parent', $parent)
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all active categories ordered by position
     *
     * @return PandaBlogCategory[]
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.is_active = 1')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Build tree (nested array) of all categories ordered by position.
     *
     * Returns array of nodes:
     * [
     *   [
     *     'category' => PandaBlogCategory,
     *     'children' => [ ... same structure ... ]
     *   ],
     *   ...
     * ]
     *
     * @return array
     */
    public function findTree(): array
    {
        $all = $this->createQueryBuilder('c')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();

        // map id => node
        $items = [];
        foreach ($all as $cat) {
            $items[$cat->get_id()] = [
                'category' => $cat,
                'children' => [],
            ];
        }

        // build tree by attaching children to parents
        foreach ($items as $id => $node) {
            $parent = $node['category']->get_parent_id();
            if ($parent instanceof PandaBlogCategory) {
                $parentId = $parent->get_id();
                if (isset($items[$parentId])) {
                    $items[$parentId]['children'][] = &$items[$id];
                }
            }
        }

        // collect roots
        $tree = [];
        foreach ($items as $id => $node) {
            $parent = $node['category']->get_parent_id();
            if (!($parent instanceof PandaBlogCategory)) {
                $tree[] = $node;
            }
        }

        return $tree;
    }


    /**
     * Pobiera kategorie po tablicy ID
     * 
     * @param int[] $ids
     * @return PandaBlogCategory[]
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}