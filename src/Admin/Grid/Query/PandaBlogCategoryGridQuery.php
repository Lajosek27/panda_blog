<?php

namespace Panda\Blog\Admin\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class PandaBlogCategoryGridQuery extends AbstractDoctrineQueryBuilder
{
    private $searchCriteriaApplicator;

    public function __construct(
        Connection $connection,
        string $dbPrefix,
        DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator
    ) {
        parent::__construct($connection, $dbPrefix);
        $this->searchCriteriaApplicator = $searchCriteriaApplicator;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {   
        
        $qb = $this->getBaseQueryBuilder();
        
        $qb->select('c.id, c.name, c.slug, COUNT(children.id) as children_count');
        $qb->leftJoin('c', $this->dbPrefix . 'panda_blog_category', 'children', 'children.parent = c.id');
        $qb->groupBy('c.id');
        
        
        // Aplikuj filtry (wyszukiwanie)
        $this->applyFilters($searchCriteria->getFilters(), $qb);

        // Aplikuj sortowanie i paginację
        $this->searchCriteriaApplicator
            ->applyPagination($searchCriteria, $qb)
            ->applySorting($searchCriteria, $qb);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQueryBuilder();
        $qb->select('COUNT(c.id)');

        // Aplikuj tylko filtry (bez sortowania i paginacji)
        $this->applyFilters($searchCriteria->getFilters(), $qb);

        return $qb;
    }

    /**
     * Bazowy QueryBuilder
     */
    private function getBaseQueryBuilder(): QueryBuilder
    {
        return $this->connection
            ->createQueryBuilder()
            ->from($this->dbPrefix . 'panda_blog_category', 'c');
    }

   private function applyFilters(array $filters, QueryBuilder $qb): void
{
    foreach ($filters as $filterName => $filterValue) {

        if ($filterValue === null || $filterValue === '') {
            continue;
        }

        switch ($filterName) {

            case 'id_parent':
                if ((int)$filterValue === 0) {
                    // root categories
                    $qb->andWhere('c.parent IS NULL');
                } else {
                    $qb->andWhere('c.parent = :parent');
                    $qb->setParameter('parent', (int)$filterValue);
                }
                break;

            case 'name':
            case 'slug':
                $qb->andWhere("c.$filterName LIKE :$filterName");
                $qb->setParameter($filterName, '%' . $filterValue . '%');
                break;

            default:
                $qb->andWhere("c.$filterName = :$filterName");
                $qb->setParameter($filterName, $filterValue);
                break;
        }
    }
}
}