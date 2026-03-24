<?php
declare(strict_types=1);

namespace Panda\Blog\Admin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use PrestaShopBundle\Form\Admin\Type\ImagePreviewType;
use PrestaShopBundle\Form\Admin\Type\EntitySearchInputType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\ChoiceCategoriesTreeType;
use PrestaShopBundle\Form\Admin\Type\FormattedTextareaType;
use Panda\Blog\Entity\PandaBlogCategory;
use Panda\Blog\Repository\PandaBlogCategoryRepository;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShopBundle\Form\Admin\Sell\Product\Description\RelatedProductType;


class PandaBlogPostFormType extends AbstractType
{
    /** @var PandaBlogCategoryRepository */
    private $categoryRepository;
    public function __construct(PandaBlogCategoryRepository $repository)
    {
        $this->categoryRepository = $repository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('id', HiddenType::class, [
                'required' => true,
            ])
            ->add('title', TextType::class, [
                'label' => 'Tytuł',
                'required' => true,
            ])
            ->add('slug', TextType::class, [
                'label' => 'Przyjazny link',
                'required' => true,
                'help' => 'Dozwolone: a-z, 0-9, oraz znaki - i _. Bez spacji i polskich znaków.'
            ])
            ->add('is_active', SwitchType::class, [
                'label' => 'Aktywna',
                'required' => false,
                'help' => 'Czy kategoria ma być widoczna na stronie',
            ])
            ->add('main_category_id', ChoiceCategoriesTreeType::class, [
                'label' => 'Kategoria główna',
                'required' => true,
                'multiple' => false,

                'list' => $options['blog_categories_tree'],
                'valid_list' => $options['blog_categories_flat'],
                'help' => 'Na tej podstawie stworzymy BreadCrumbs na stronie wpisu'
            ])

            ->add('categories', ChoiceCategoriesTreeType::class, [
                'label' => 'Kategorie dodatkowe',
                'required' => false,
                'multiple' => true,
                'list' => $options['blog_categories_tree'],
                'valid_list' => $options['blog_categories_flat'],
                'help' => 'Wybierz kategorie gdzie chcesz wyświetlać wpis.'
            ])
            ->add('content', FormattedTextareaType::class, [
                'label' => 'Treść',
                'required' => false,

            ])
            ->add('excerpt', FormattedTextareaType::class, [
                'label' => 'Zajawka',
                'required' => false,
            ])
            ->add('image_preview', ImagePreviewType::class, [
                'label' => 'Obrazek wyróżniający',
                'required' => false,
                'data_class' => null,
            ])
            ->add('image', FileType::class, [
                'label' => 'Obrazek wyróżniający',
                'required' => false,
                'attr' => [
                    'accept' => 'image/*',
                ],
                'help' => 'Dozwolone formaty: JPG, PNG, GIF. Maksymalny rozmiar: 2MB',
            ])
            ->add('meta_title', TextType::class, [
                'label' => 'Meta tytuł',
                'required' => false,
                'help' => 'Jeśli puste użyjemy głównego tytułu'
            ])
            ->add('meta_description', TextareaType::class, [
                'label' => 'Meta Opis',
                'required' => false,
            ])
            ->add('related_products', EntitySearchInputType::class, [
                'label' => 'Powiązane produkty',
                'entry_type' => RelatedProductType::class,
                'remote_url' => $options['product_search_url'],
                'limit' => 10,
                'min_length' => 2,
                'placeholder' => 'Wpisz nazwę produktu...',
                'allow_search' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype_mapping' => [
                    'id' => '__id__',
                    'name' => '__name__',
                    'image' => '__image__',
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $defData = $this->getBlogCategoriesTrees();
        $product_search_url = $this->getProductSearchUrl();
        $resolver->setDefaults([
            'data_class' => null,
            'blog_categories_tree' => $defData['tree'],
            'blog_categories_flat' => $defData['flat'],
            'product_search_url' => $product_search_url,
        ]);
    }
    private function getProductSearchUrl(): ?string
    {
        $url = null;
        $sfContainer = SymfonyContainer::getInstance();
        if (null !== $sfContainer) {
            $sfRouter = $sfContainer->get('router');
           
            $url = $sfRouter->generate(
                'admin_products_search_products_for_association',
                [
                    'languageCode' => \Context::getContext()->language->language_code,
                    'query' => '__QUERY__',
                ]
            );
        }

        return $url ?? null;
    }

    private function getBlogCategoriesTrees(): array
    {
        $allCategories = $this->categoryRepository->findAll();
        $tree = $this->buildCategoryTree($allCategories);
        $flat = array_map(function ($cat) {
            return $cat->getId();
        }, $allCategories);


        return [
            'tree' => $tree,
            'flat' => $flat,
        ];
    }
    /**
     * Buduje drzewo kategorii w formacie wymaganym przez ChoiceCategoriesTreeType
     * 
     * @param PandaBlogCategory[] $categories
     * @param int|null $parentId
     * @return array
     */
    private function buildCategoryTree(array $categories, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($categories as $category) {
            $categoryParentId = $category->getParent() ? $category->getParent()->getId() : null;

            if ($categoryParentId === $parentId) {
                $node = [
                    'id_category' => $category->getId(), // Ważne: id_category, nie id!
                    'name' => $category->getName(),
                    'children' => $this->buildCategoryTree($categories, $category->getId()),
                ];

                $tree[] = $node;
            }
        }

        return $tree;
    }

}