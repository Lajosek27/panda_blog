<?php

namespace Panda\Blog\Admin\Grid\Filters;

use Panda\Blog\Admin\Grid\Definition\PandaBlogPostGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

class PandaBlogPostFilters extends Filters
{   
    protected $filterId = PandaBlogPostGridDefinitionFactory::GRID_ID;

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