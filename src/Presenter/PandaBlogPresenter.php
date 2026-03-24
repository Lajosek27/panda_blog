<?php


namespace Panda\Blog\Presenter;


use Panda\Blog\Entity\PandaBlogPost;
use Panda\Blog\Entity\PandaBlogCategory;
use Doctrine\Common\Collections\Collection;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class PandaBlogPresenter
{
    public function present($entity): array
    {
        if ($entity instanceof PandaBlogPost) {
            return $this->presentPost($entity);
        }

        if ($entity instanceof PandaBlogCategory) {
            return $this->presentCategory($entity);
        }

        throw new \InvalidArgumentException(
            'Unsupported entity type: ' . (is_object($entity) ? get_class($entity) : gettype($entity))
        );
    }

    public function presentCollection(array|Collection $entities): array
    {


        if ($entities instanceof Collection) {
            $entities = $entities->toArray();
        }

        return array_values(array_map(
            fn($entity) => $this->present($entity),
            $entities
        ));
    }

    // =====================================================
    // URL HELPERS
    // =====================================================

    private function getBaseUrl(): string
    {
        $baseUrl = (string) \ConfigurationCore::get('PANDA_BLOG_BASE_URL');

        if (!$baseUrl) {
            $baseUrl = 'blog';
        }

        return trim($baseUrl, '/');
    }

    private function getShopDomain(): string
    {
        // Presta standard
        $domain = defined('_PS_BASE_URL_SSL_') ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_;

        return rtrim($domain, '/');
    }

    private function getCategoryUrl(PandaBlogCategory $category): string
    {
        return sprintf(
            '%s/%s/%s',
            $this->getShopDomain(),
            $this->getBaseUrl(),
            $category->getSlug()
        );
    }

    private function getPostUrl(PandaBlogPost $post): ?string
    {
        $category = $this->getPostCategoryForUrl($post);

        if (!$category) {
            return null;
        }

        return sprintf(
            '%s/%s/%s/%s',
            $this->getShopDomain(),
            $this->getBaseUrl(),
            $category->getSlug(),
            $post->getSlug()
        );
    }

    private function getPostCategoryForUrl(PandaBlogPost $post): ?PandaBlogCategory
    {
        if ($post->getMainCategory()) {
            return $post->getMainCategory();
        }

        $cats = $post->getCategories()->toArray();

        return $cats[0] ?? null;
    }

    // =====================================================
    // PRESENTERS
    // =====================================================

    private function presentCategory(PandaBlogCategory $category): array
    {
        return [
            'type' => 'category',
            'id' => $category->getId(),
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
            'url' => $this->getCategoryUrl($category),

            'description' => $category->getDescription(),

            'meta' => [
                'title' => $category->getMetaTitle(),
                'description' => $category->getMetaDescription(),
            ],

            'is_active' => $category->getIsActive(),
            'position' => $category->getPosition(),

            'parent' => $category->getParent()
                ? $this->presentCategorySmall($category->getParent())
                : null,

            'children' => array_values(array_map(
                fn($child) => $this->presentCategorySmall($child),
                $category->getChildren()->toArray()
            )),

            'date_add' => $category->getDateAdd()?->format('Y-m-d H:i:s'),
            'date_upd' => $category->getDateUpd()?->format('Y-m-d H:i:s'),
        ];
    }

    private function presentPost(PandaBlogPost $post): array
    {
        $urlCategory = $this->getPostCategoryForUrl($post);

        return [
            'type' => 'post2',
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'slug' => $post->getSlug(),
            'url' => $this->getPostUrl($post),

            'excerpt' => $post->getExcerpt(),
            'content' => $post->getContent(),
            'image' => $this->getImage($post->getImage()),

            'meta' => [
                'title' => $post->getMetaTitle(),
                'description' => $post->getMetaDescription(),
            ],

            'is_active' => $post->getIsActive(),

            'main_category' => $post->getMainCategory()
                ? $this->presentCategorySmall($post->getMainCategory())
                : null,

            'categories' => array_values(array_map(
                fn($cat) => $this->presentCategorySmall($cat),
                $post->getCategories()->toArray()
            )),
            // kategoria użyta do generowania URL-a
            'url_category' => $urlCategory
                ? $this->presentCategorySmall($urlCategory)
                : null,

            'author' => $post->getAuthor(),
            'date_add' => $post->getDateAdd()?->format('Y-m-d H:i:s'),
            'date_upd' => $post->getDateUpd()?->format('Y-m-d H:i:s'),
        ];
    }
    private function getImage(?string $img_name): ?string
    {
        if (!$img_name)
            return null;

        return sprintf(
            '%s/%s/%s',
            $this->getShopDomain(),
            'img/panda_blog',
            $img_name
        );
    }
    private function presentCategorySmall(PandaBlogCategory $category): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
            'url' => $this->getCategoryUrl($category),
        ];
    }

    public static function getBlogConfiguration()
    {
        return [
            'PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN' => (bool) \ConfigurationCore::get('PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN'),
            'PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM' => (bool) \ConfigurationCore::get('PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM'),
            'PANDA_BLOG_BASE_URL' => (string) \ConfigurationCore::get('PANDA_BLOG_BASE_URL'),

        ];
    }

    public static function getImagePath(?string $img_name): ?string
    {
        if (!$img_name)
            return null;

        $image_path = sprintf(
            '%s/%s/%s',
            _PS_ROOT_DIR_,
            'img/panda_blog',
            $img_name
        );
        return file_exists($image_path) ? $image_path : null;
    }

    public static function getImageUrlStatic(?string $image_name, bool $with_domain = true): ?string
    {
        $res = null;
        if ($with_domain) {
            return (new self)->getImage($image_name);
        } else {
            return "/img/panda_blog/$image_name";
        }
    }


    public function presentProducts(?array $ids)
    {
        if (null == $ids || !is_array($ids)) {
            return [];
        }

        $context = \Context::getContext();
        $assembler = new \ProductAssembler($context);
        $presenterFactory = new \ProductPresenterFactory($context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter(
            new ImageRetriever(
                $context->link
            ),
            $context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $context->getTranslator()
        );
        $res = [];
        foreach ($ids as $id) {
            $res[$id] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct(['id_product' => (int) $id]),
                $context->language
            );
        }

        $products_for_templates = array_filter($res,function ($product) {
            return 1;
            // return $product['quantity'] > 0 && $product['active'] == 1;
        });



        return $products_for_templates ?? [];
    }


}
