<?php

declare(strict_types=1);

namespace Panda\Blog\Admin\Form\Data\Configuration;

use DbQueryCore;
use Panda\Blog\Presenter\PandaBlogPresenter;
use Symfony\Component\HttpFoundation\RequestStack;
use Panda\Blog\Repository\PandaBlogPostRepository;
use Panda\Blog\Repository\PandaBlogCategoryRepository;
use Panda\Blog\Entity\PandaBlogPost;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductPresentationSettings;

final class PandaBlogPostDataConfiguration extends AbstractPandaDataConfiguration
{
    /** @var PandaBlogPostRepository */
    private $postRepository;

    /** @var PandaBlogCategoryRepository */
    private $categoryRepository;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        PandaBlogPostRepository $postRepository,
        PandaBlogCategoryRepository $categoryRepository,
        RequestStack $requestStack
    ) {
        $this->postRepository = $postRepository;
        $this->categoryRepository = $categoryRepository;
        $this->requestStack = $requestStack;
    }

    public function getConfiguration(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $idPost = $request ? (int) $request->attributes->get('id', 0) : 0;



        // Nowy post - zwróć pusty formularz
        if ($idPost === 0) {
            return $this->getEmptyPostData();
        }

        try {
            // Pobierz post z bazy
            $post = $this->postRepository->find($idPost);

            // Jeśli post nie istnieje
            if ($post === null) {
                \PrestaShopLoggerCore::addLog(
                    sprintf('Post bloga o ID %d nie został znaleziony', $idPost),
                    2, // Warning
                    null,
                    'PandaBlogPost'
                );
                return $this->getEmptyPostData();
            }

            $main_category_id = $post->getMainCategory()?->getId();
            $image = $post->getImage() ? PandaBlogPresenter::getImageUrlStatic($post->getImage(), false) : null;

            $releted_products_ids = $post->getRelatedProductIds();
            $releted_product = $this->prepereRelatedProducts($releted_products_ids);

            // Zwróć dane istniejącego posta
            return [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'is_active' => $post->getIsActive(),
                'content' => $post->getContent(),
                'excerpt' => $post->getExcerpt(),
                'meta_title' => $post->getMetaTitle(),
                'meta_description' => $post->getMetaDescription(),
                'image_preview' => $image,
                'main_category_id' => $main_category_id ?
                    ['tree' => [$main_category_id]] : null,

                'categories' => [
                    'tree' => $post->getCategories()
                        ? $post->getCategories()->map(fn($c) => $c->getId())->toArray()
                        : []
                ],
                'related_products' => $this->prepereRelatedProducts($post->getRelatedProductIds())

            ];

        } catch (\Exception $e) {
            \PrestaShopLoggerCore::addLog(
                sprintf('Błąd podczas pobierania posta bloga ID %d: %s', $idPost, $e->getMessage()),
                3, // Error
                null,
                'PandaBlogPost'
            );
            return $this->getEmptyPostData();
        }
    }
    private function prepereRelatedProducts(array $related_products_ids)
    {
        if (empty($related_products_ids))
            return [];
        return array_map(function ($rp) {
            return [
                'id' => $rp,
                'name' => \Product::getProductName($rp, 0, \Context::getContext()->language->id),
                'image' => $this->getProductImageUrl((int) $rp)
            ];
        }, $related_products_ids);
        
    }


    private function  getProductImageUrl(int $id_product): string
    {
        $context = \Context::getContext();
        $imageRetriever = new ImageRetriever($context->link);

        // Pobierz pierwsze zdjęcie produktu
        $coverImage = $imageRetriever->getImage(
            new \Product($id_product, false, $context->language->id),
            \Image::getCover($id_product)['id_image'] ?? 0
        );

        return $coverImage['small']['url'] ?? '';
    }
    /**
     * Zwraca pustą strukturę danych dla nowego formularza posta
     */
    private function getEmptyPostData(): array
    {
        return [
            'id' => 0,
            'title' => '',
            'slug' => '',
            'is_active' => true,
            'content' => '',
            'excerpt' => '',
            'meta_title' => '',
            'meta_description' => '',
            'main_category_id' => null,
            'related_products' => null
        ];
    }

    public function updateConfiguration(array $configuration): array
    {
        $this->validateConfiguration($configuration);

        if (!$this->hasErrors()) {
            try {

                $idPost = (int) ($configuration['id'] ?? 0);

                if ($idPost > 0) {
                    $post = $this->postRepository->find($idPost);
                    if ($post === null) {
                        $this->setField('id');
                        $this->addError(self::ERROR_NOT_FOUND, ['wpis', $idPost]);
                        $this->clearField();
                        return $this->getErrors();
                    }
                } else {
                    $post = new PandaBlogPost();
                }
                $post->setTitle($configuration['title']);
                $post->setSlug($configuration['slug']);
                $post->setContent($configuration['content'] ?? null);
                $post->setExcerpt($configuration['excerpt'] ?? null);
                $post->setMetaTitle($configuration['meta_title'] ?? null);
                $post->setMetaDescription($configuration['meta_description'] ?? null);
                $post->setIsActive($configuration['is_active'] ?? true);
                $post->setAuthor(\Context::getContext()->employee->firstname);

                $main_category = $this->categoryRepository->findById((int) $configuration['main_category_id']);
                $post->setMainCategory($main_category);


                $extraCatsIds = $configuration['categories']['tree'] ?? [];
                $currentCategories = $post->getCategories();

                if (!in_array($main_category->getId(), $extraCatsIds)) {
                    $extraCatsIds[] = $main_category->getId();
                }

                foreach ($currentCategories as $currentCategory) {
                    if (!in_array($currentCategory->getId(), $extraCatsIds)) {
                        $post->removeCategory($currentCategory);
                    }
                }

                $categories = $this->categoryRepository->findByIds($extraCatsIds);
                foreach ($categories as $category) {
                    if ($category) {
                        $post->addCategory($category);
                    }
                }
                $old_img = $post->getImage();
                $this->handleImgUpload($post, $configuration);


                $related_products = [];
                $related_products_data = $configuration['related_products'];
                foreach ($related_products_data as $product_data) {
                    $related_products[] = $product_data['id'];
                }
                $post->setRelatedProductIds($related_products);

                // Walidacja unikalności sluga
                if ($this->postRepository->slugExists($configuration['slug'], $idPost)) {
                    $this->setField('slug', 'przyjazny link');
                    $this->addError(self::ERROR_DUPLICATE);
                    $this->clearField();
                    return $this->getErrors();
                }

                $this->postRepository->save($post);

                $this->handleDeleteImg($post, $old_img);
            } catch (\Exception $e) {
                \PrestaShopLoggerCore::addLog('PandaBlog Post: ' . $e->getMessage(), 3);
                $this->addError(self::ERROR_SAVE_FAILED);
            }
        }

        return $this->getErrors();
    }


    public function handleImgUpload(PandaBlogPost $post, array $configuration): void
    {
        $file = $configuration['image'] ?? null;

        if (!$file instanceof UploadedFile) {
            return; // brak pliku = OK
        }

        $this->setField('image', 'zdjęcie');

        if (!$file->isValid()) {
            $this->addError(self::ERROR_SAVE_FAILED);
            return;
        }

        $mime = (string) $file->getMimeType();
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/jpg',
            'image/pjpeg',
        ];

        if (!in_array($mime, $allowedMimes, true)) {
            $this->addError(self::ERROR_SAVE_FAILED);
            return;
        }

        // max 2MB
        $maxSize = 2 * 1024 * 1024;
        if ((int) $file->getSize() > $maxSize) {
            $this->addError(self::ERROR_SAVE_FAILED);
            return;
        }

        // Oryginalna nazwa pliku bez rozszerzenia
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Rozszerzenie: najpierw guessExtension(), jak puste to mapujemy po MIME
        $extension = strtolower((string) $file->guessExtension());

        if ($extension === '') {
            $mimeToExt = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/pjpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];
            $extension = $mimeToExt[$mime] ?? '';
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
            $this->addError(self::ERROR_SAVE_FAILED);
            return;
        }

        // Katalog docelowy
        $uploadDir = _PS_IMG_DIR_ . 'panda_blog/';

        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                $this->addError(self::ERROR_SAVE_FAILED);
                return;
            }
        }

        if (!is_writable($uploadDir)) {
            $this->addError(self::ERROR_SAVE_FAILED);
            return;
        }

        // Generuj nazwę: oryginalna-nazwa-8znakow-z-hasha.jpg
        $hash = substr(bin2hex(random_bytes(4)), 0, 8); // 8 znaków hex
        $safeName = preg_replace('/[^a-z0-9_-]/i', '-', $originalName); // Bezpieczna nazwa (usuń polskie znaki itp)
        $fileName = $safeName . '-' . $hash . '.' . $extension;

        try {
            $file->move($uploadDir, $fileName);
        } catch (\Throwable $e) {
            $this->addError(self::ERROR_SAVE_FAILED);
            return;
        }

        // dopiero po udanym move ustawiamy w encji + sprzątamy stary plik
        $oldImage = $post->getImage();
        $post->setImage($fileName);

        if (!empty($oldImage) && $oldImage !== $fileName) {
            $oldPath = $uploadDir . $oldImage;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }
    }

    private function handleDeleteImg(PandaBlogPost $post, ?string $oldImage): void
    {


        // Pobierz aktualny obrazek z encji
        $newImage = $post->getImage();

        // Usuń stary plik TYLKO jeśli nowy obrazek jest inny
        if ($oldImage !== $newImage) {
            $uploadDir = _PS_IMG_DIR_ . 'panda_blog/';
            $oldPath = $uploadDir . $oldImage;

            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }
    }
    public function validateConfiguration(array $configuration): bool
    {
        $this->clearErrors();

        /*
         * TITLE (required, max 255)
         */
        $this->setField('title', 'tytuł');
        if (empty(trim((string) ($configuration['title'] ?? '')))) {
            $this->addError(self::ERROR_EMPTY);
        } else {
            $title = trim((string) $configuration['title']);
            if (mb_strlen($title) > 255) {
                $this->addError(self::ERROR_TOO_LONG, [255]);
            }
        }

        /*
         * SLUG (required, max 255, format)
         */
        $this->setField('slug', 'przyjazny link');
        if (empty(trim((string) ($configuration['slug'] ?? '')))) {
            $this->addError(self::ERROR_EMPTY);
        } else {
            $slug = trim((string) $configuration['slug']);

            if (mb_strlen($slug) > 255) {
                $this->addError(self::ERROR_TOO_LONG, [255]);
            }

            // format: a-z, 0-9, -, _
            if (!preg_match('/^[a-z0-9\-_]+$/', $slug)) {
                $this->addError(self::ERROR_INVALID);
            }
        }

        /*
         * CONTENT (nullable, HTML allowed)
         */
        $this->setField('content', 'treść');
        // Content może być pusty lub zawierać HTML - nie walidujemy długości dla TEXT
        // Opcjonalnie możesz dodać sanityzację HTML tutaj

        /*
         * META TITLE (nullable, max 255)
         */
        $this->setField('meta_title', 'Meta tytuł');
        if (!empty($configuration['meta_title'])) {
            $metaTitle = trim((string) $configuration['meta_title']);
            if (mb_strlen($metaTitle) > 255) {
                $this->addError(self::ERROR_TOO_LONG, [255]);
            }
        }

        /*
         * META DESCRIPTION (nullable, max 512)
         */
        $this->setField('meta_description', 'meta opis');
        if (!empty($configuration['meta_description'])) {
            $metaDesc = trim((string) $configuration['meta_description']);
            if (mb_strlen($metaDesc) > 512) {
                $this->addError(self::ERROR_TOO_LONG, [512]);
            }
        }

        /*
         * IS ACTIVE (boolean)
         */
        $this->setField('is_active', 'aktywna');
        if (isset($configuration['is_active'])) {
            if (!in_array($configuration['is_active'], [0, 1, true, false, '0', '1'], true)) {
                $this->addError(self::ERROR_INVALID);
            }
        }

        /*
         * MAIN CATEGORY ID (INT)
         */
        $this->setField('main_category_id', 'kategoria główna');
        if (isset($configuration['main_category_id'])) {
            $this->validateCategoryRelation((int) $configuration['main_category_id']);
        } else {
            $this->addError(self::ERROR_EMPTY);
        }

        /*
         * CATEGORIES (RELATION)
         */
        $this->setField('categories', 'kategorie dodatkowe');
        if (isset($configuration['categories']['tree']) && !empty($configuration['categories']['tree'])) {
            foreach ($configuration['categories']['tree'] as $category_id) {
                $this->validateCategoryRelation((int) $category_id);
            }
        }
        /*
         * RELATED_PRODUCTS (RELATION)
         */
        $this->setField('related_products', 'Powiązane produkty');
        if (isset($configuration['related_products']) && !empty($configuration['related_products'])) {
            foreach ($configuration['related_products'] as $product) {
                if (isset($product['id']) && !empty($product['id'])) {
                    $this->validateProductRelation((int) $product['id']);
                } else {

                }
            }
        }

        $this->clearField();

        return !$this->hasErrors();
    }

    private function validateCategoryRelation(?int $category_id): void
    {
        $this->setField('categories', 'kategorie dodatkowe');
        if ($category_id == null) {
            $this->addError(self::ERROR_NOT_FOUND, ['kategoria', $category_id]);
            return;
        }
        if ($category_id > 0) {
            $parent = $this->categoryRepository->findById($category_id);
            if ($parent == null) {
                $this->addError(self::ERROR_NOT_FOUND, ['kategoria', $category_id]);
            }
        } else {
            $this->addError(self::ERROR_NOT_FOUND, ['kategoria', $category_id]);
        }
    }
    private function validateProductRelation(?int $product_id): void
    {
        $this->setField('related_products', 'powiązane produkty');
        if ($product_id == null) {
            $this->addError(self::ERROR_NOT_FOUND, ['produkt', $product_id]);
            return;
        }
        if ($product_id > 0) {
            $exists = \Db::getInstance()->getValue(
                (new \DbQuery())
                    ->select('p.id_product')
                    ->from('product_shop', 'p')
                    ->where("p.active = 1 and p.id_shop =" . (int) \Context::getContext()->shop->id)
                    ->where("p.id_product = $product_id")
            );
            if ($exists == null) {
                $this->addError(self::ERROR_NOT_FOUND, ['produkt', $product_id]);
            }
        } else {
            $this->addError(self::ERROR_NOT_FOUND, ['produkt', $product_id]);
        }
    }
}