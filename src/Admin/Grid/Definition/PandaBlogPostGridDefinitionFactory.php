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
use PrestaShopBundle\Form\Admin\Type\YesAndNoChoiceType;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\LinkColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ToggleColumn;


class PandaBlogPostGridDefinitionFactory extends AbstractFilterableGridDefinitionFactory
{

    public const GRID_ID = 'panda_blog_post';
    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return $this->trans('Wpisy', [], 'Module.Pandablog.Admin');
    }

    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add(
                (new LinkColumn('id'))
                    ->setName($this->trans('ID', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'id',
                        'route' => 'panda_blog_post.edit',
                        'route_param_name' => 'id',
                        'route_param_field' => 'id',
                        'color_template' => 'secondary'

                    ])
            )
            ->add(
                (new DataColumn('title'))
                    ->setName($this->trans('Nazwa', [], 'Admin.Advparameters.Feature'))
                    ->setOptions([
                        'field' => 'title',

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
                (new DataColumn('main_category'))
                    ->setName($this->trans('Kategoria', [], 'Admin.Advparameters.Feature'))
                    ->setOptions([
                        'field' => 'main_category',
                    ])
            )
            ->add(
                (new ToggleColumn('is_active'))
                    ->setName($this->trans('Aktywny', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'is_active',
                        'primary_field' => 'id',
                        'route' => 'panda_blog_post.toggle_active',
                        'route_param_name' => 'id',

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
                        'route' => 'panda_blog_post.edit',
                        'route_param_name' => 'id',
                        'route_param_field' => 'id',
                    ])
            )
            ->add(
                (new LinkRowAction('delete'))
                    ->setName($this->trans('Usuń', [], 'Admin.Actions'))
                    ->setIcon('delete')
                    ->setOptions([
                        'route' => 'panda_blog_post.delete',
                        'route_param_name' => 'id',
                        'route_param_field' => 'id',
                        'confirm_message' => $this->trans(
                            'Czy na pewno chcesz usunąć tę kategorię?',
                            [],
                            'Modules.Pandablog.Admin'
                        ),
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
                (new Filter('title', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('Szukaj nazwy', [], 'Admin.Actions'),
                        ],
                    ])
                    ->setAssociatedColumn('title')
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
                (new Filter('main_category', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('Szukaj kategorii', [], 'Admin.Actions'),
                        ],
                    ])
                    ->setAssociatedColumn('main_category')
            )
            ->add(
                (new Filter('is_active', YesAndNoChoiceType::class)) 
                    ->setTypeOptions([
                        'required' => false,
                    ])
                    ->setAssociatedColumn('is_active')
            )
            ->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => self::GRID_ID,
                        ],
                        'redirect_route' => 'panda_blog_post.list',
                    ])
                    ->setAssociatedColumn('actions')
            )
        ;
    }
}