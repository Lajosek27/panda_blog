<?php

namespace Panda\Blog\Admin\Grid\Filters;

use Panda\Blog\Admin\Grid\Definition\PandaBlogCategoryGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

class PandaBlogCategoryFilters extends Filters
{   
    protected $filterId = PandaBlogCategoryGridDefinitionFactory::GRID_ID;

    public static function getDefaults(): array
    {
        return [
            'limit' => null,
            'offset' => null,
            'orderBy' => 'id',
            'sortOrder' => 'ASC',
            'filters' => [],
        ];
    }
}