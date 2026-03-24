<?php

namespace Panda\Blog\Admin\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class PandaBlogPostGridQuery extends AbstractDoctrineQueryBuilder
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
        
        $qb->select('a.id, a.title, a.slug,a.is_active,a.date_add,c.name as main_category');
        $qb->leftJoin('a',$this->dbPrefix .'panda_blog_category','c','a.main_category_id = c.id');
        
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
        $qb->select('COUNT(a.id)');

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
            ->from($this->dbPrefix . 'panda_blog_post', 'a');
    }

   private function applyFilters(array $filters, QueryBuilder $qb): void
{
    foreach ($filters as $filterName => $filterValue) {

        if ($filterValue === null || $filterValue === '') {
            continue;
        }

        switch ($filterName) {

            case 'main_category':
                $qb->leftJoin('a',$this->dbPrefix.'panda_blog_category','main_cat','main_cat.id = a.main_category_id');
                 $qb->andWhere("main_cat.name LIKE :$filterName");
                $qb->setParameter($filterName, '%' . $filterValue . '%');
                break;

            case 'title':
            case 'slug':
            
                $qb->andWhere("a.$filterName LIKE :$filterName");
                $qb->setParameter($filterName, '%' . $filterValue . '%');
                break;

            default:
                $qb->andWhere("a.$filterName = :$filterName");
                $qb->setParameter($filterName, $filterValue);
                break;
        }
    }
}
}