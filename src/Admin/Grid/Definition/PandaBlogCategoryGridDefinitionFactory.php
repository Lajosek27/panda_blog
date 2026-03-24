<?php

namespace Panda\Blog\Admin\Grid\Definition;

use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractFilterableGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\LinkColumn;


class PandaBlogCategoryGridDefinitionFactory extends AbstractFilterableGridDefinitionFactory
{

    public const GRID_ID = 'panda_blog_category';
    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return $this->trans('Kategorie bloga', [], 'Module.Pandablog.Admin');
    }

    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add(
                (new LinkColumn('id'))
                    ->setName($this->trans('ID', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'id',
                         'route' => 'panda_blog_category.list',
                        'route_param_name' => 'id_parent',
                        'route_param_field' => 'id',
                        'color_template' => 'secondary'
                        
                    ])
            )
            ->add(
                (new DataColumn('name'))
                    ->setName($this->trans('Nazwa', [], 'Admin.Advparameters.Feature'))
                    ->setOptions([
                        'field' => 'name',
                       
                    ])
            )
            ->add(
                (new DataColumn('slug'))
                    ->setName($this->trans('Link', [], 'Admin.Advparameters.Feature'))
                    ->setOptions([
                        'field' => 'slug',
                    ])
            )
            ->add(
                (new ActionColumn('actions'))
                    ->setName('Akcje')
                    ->setOptions([
                        'actions' => $this->getRowActions(),
                    ])
            );
        ;
    }

    private function getRowActions(): RowActionCollection
    {
        return (new RowActionCollection())
            ->add(
                (new LinkRowAction('edit'))
                    ->setName($this->trans('Edytuj', [], 'Admin.Actions'))
                    ->setIcon('edit')
                    ->setOptions([
                        'route' => 'panda_blog_category.edit',
                        'route_param_name' => 'id',
                        'route_param_field' => 'id',
                    ])
            )
            ->add(
                (new LinkRowAction('delete'))
                    ->setName($this->trans('Usuń', [], 'Admin.Actions'))
                    ->setIcon('delete')
                    ->setOptions([
                        'route' => 'panda_blog_category.delete',
                        'route_param_name' => 'id',
                        'route_param_field' => 'id',
                        'confirm_message' => $this->trans(
                            'Czy na pewno chcesz usunąć tę kategorię?',
                            [],
                            'Modules.Pandablog.Admin'
                        ),
                    ])
            )
            ->add(
                (new LinkRowAction('view_children'))
                    ->setName('Zobacz dzieci')
                    ->setIcon('visibility')
                    ->setOptions([
                        'route' => 'panda_blog_category.list',
                        'route_param_name' => 'id_parent',
                        'route_param_field' => 'id',
                        'clickable_row' => true, 
                    ])
            )
        ;
    }


    protected function getFilters()
    {
        return (new FilterCollection())
            ->add(
                (new Filter('id', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('ID', [], 'Admin.Global'),
                        ],
                    ])
                    ->setAssociatedColumn('id')
            )
            ->add(
                (new Filter('name', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('Szukaj nazwy', [], 'Admin.Actions'),
                        ],
                    ])
                    ->setAssociatedColumn('name')
            )
            ->add(
                (new Filter('slug', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('Szukaj linku', [], 'Admin.Actions'),
                        ],
                    ])
                    ->setAssociatedColumn('slug')
            )
            ->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => self::GRID_ID,
                        ],
                        'redirect_route' => 'panda_blog_category.filter',
                    ])
                    ->setAssociatedColumn('actions')
            )
        ;
    }
}