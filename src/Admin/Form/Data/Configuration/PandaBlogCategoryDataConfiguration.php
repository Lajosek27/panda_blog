<?php

declare(strict_types=1);

namespace Panda\Blog\Admin\Form\Data\Configuration;


use Symfony\Component\HttpFoundation\RequestStack;
use Panda\Blog\Repository\PandaBlogCategoryRepository;
use Panda\Blog\Entity\PandaBlogCategory;

final class PandaBlogCategoryDataConfiguration extends AbstractPandaDataConfiguration
{

    /** @var PandaBlogCategoryRepository */
    private $repository;

    /** @var RequestStack */
    private $requestStack;


    public function __construct(PandaBlogCategoryRepository $repository, RequestStack $requestStack)
    {
        $this->repository = $repository;
        $this->requestStack = $requestStack;
    }

    public function getConfiguration(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $idCategory = $request ? (int) $request->attributes->get('id', 0) : 0;

        // Nowa kategoria - zwróć pusty formularz
        if ($idCategory === 0) {
            return $this->getEmptyCategoryData();
        }

        try {
            // Pobierz kategorię z bazy
            $category = $this->repository->findById($idCategory);

            // Jeśli kategoria nie istnieje
            if ($category === null) {
                \PrestaShopLoggerCore::addLog(
                    sprintf('Kategoria bloga o ID %d nie została znaleziona', $idCategory),
                    2, // Warning
                    null,
                    'PandaBlogCategory'
                );
                return $this->getEmptyCategoryData();
            }

            // Zwróć dane istniejącej kategorii
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'id_parent' => $category->getParentId(),
                'description' => $category->getDescription(),
                'meta_title' => $category->getMetaTitle(),
                'meta_description' => $category->getMetaDescription(),
                'is_active' => $category->getIsActive(),
            ];

        } catch (\Exception $e) {
            \PrestaShopLoggerCore::addLog(
                sprintf('Błąd podczas pobierania kategorii bloga ID %d: %s', $idCategory, $e->getMessage()),
                3, // Error
                null,
                'PandaBlogCategory'
            );
            return $this->getEmptyCategoryData();
        }
    }


    /**
     * Zwraca pustą strukturę danych dla nowego formularza kategorii
     */
    private function getEmptyCategoryData(): array
    {
        return [
            'id' => 0,
            'name' => '',
            'slug' => '',
            'id_parent' => null,
            'description' => '',
            'meta_title' => '',
            'meta_description' => '',
            'is_active' => true,
        ];
    }

    public function updateConfiguration(array $configuration): array
    {
        $this->validateConfiguration($configuration);

        if (!$this->hasErrors()) {
            try {
                $idCategory = (int) ($configuration['id'] ?? 0);

                if ($idCategory > 0) {
                    $category = $this->repository->findById($idCategory);
                    if ($category === null) {
                        $this->setField('id');
                        $this->addError(self::ERROR_NOT_FOUND);
                        $this->clearField();
                        return $this->getErrors();
                    }
                } else {
                    $category = new PandaBlogCategory();
                }

                $category->setName($configuration['name']);
                $category->setSlug($configuration['slug']);
                $category->setDescription($configuration['description'] ?? null);
                $category->setMetaTitle($configuration['meta_title'] ?? null);
                $category->setMetaDescription($configuration['meta_description'] ?? null);
                $category->setIsActive($configuration['is_active'] ?? true);
                
                //TODO: zrobić możliwość przesuwania oraz liczenia i zmiany pozycji.
                $category->setPosition($configuration['position'] ?? 0);


                $id_parent = $configuration['id_parent'] ?? null;
                if ($id_parent !== null) {
                    $parent = $this->repository->find($id_parent);
                    $category->setParent($parent);

                    $parent->addChild($category);
                } else {
                    $category->setParent(null);
                }


                // Walidacja unikalności sluga
                $existingCategory = $this->repository->findBySlug($configuration['slug']);
                if ($existingCategory !== null && $existingCategory->getId() !== $idCategory) {
                    $this->setField('slug');
                    $this->addError(self::ERROR_DUPLICATE);
                    $this->clearField();
                    return $this->getErrors();
                }

                $this->repository->save($category);
                if ($id_parent !== null) {
                    $this->repository->save($parent);
                }

            } catch (\Exception $e) {
                \PrestaShopLoggerCore::addLog('PandaBlog: ' . $e->getMessage(), 3);
                $this->addError(self::ERROR_SAVE_FAILED);
            }
        }

        return $this->getErrors();
    }

    public function validateConfiguration(array $configuration): bool
    {
        $this->clearErrors();

        /*
         * NAME (required, max 255)
         */
        $this->setField('name');
        if (empty(trim((string) ($configuration['name'] ?? '')))) {
            $this->addError(self::ERROR_EMPTY);
        } else {
            $name = trim((string) $configuration['name']);
            if (mb_strlen($name) > 255) {
                $this->addError(self::ERROR_TOO_LONG, [255]);
            }
        }

        /*
         * SLUG (required, max 255, format)
         */
        $this->setField('slug');
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
         * META TITLE (nullable, max 255)
         */
        $this->setField('meta_title');
        if (!empty($configuration['meta_title'])) {
            $metaTitle = trim((string) $configuration['meta_title']);
            if (mb_strlen($metaTitle) > 255) {
                $this->addError(self::ERROR_TOO_LONG, [255]);
            }
        }

        /*
         * META DESCRIPTION (nullable, max 512)
         */
        $this->setField('meta_description');
        if (!empty($configuration['meta_description'])) {
            $metaDesc = trim((string) $configuration['meta_description']);
            if (mb_strlen($metaDesc) > 512) {
                $this->addError(self::ERROR_TOO_LONG, [512]);
            }
        }

        /*
         * IS ACTIVE (boolean)
         */
        $this->setField('is_active');
        if (isset($configuration['is_active'])) {
            if (!in_array($configuration['is_active'], [0, 1, true, false, '0', '1'], true)) {
                $this->addError(self::ERROR_INVALID);
            }
        }

        /*
         * POSITION (int >= 0)
         */
        $this->setField('position');
        if (isset($configuration['position'])) {
            if (!is_numeric($configuration['position']) || (int) $configuration['position'] < 0) {
                $this->addError(self::ERROR_INVALID);
            }
        }

        /*
         * id_parent (opcjonalne – tylko jeśli chcesz sprawdzać czy to int)
         */
        $this->setField('id_parent');
        $this->validateParentId($configuration);

        $this->clearField();

        return !$this->hasErrors();
    }

    private function validateSlug(string $value): bool
    {
        $slug = trim($value);

        // Długość
        if (mb_strlen($slug) < 2) {
            $this->addError(self::ERROR_TOO_SHORT, [2]); // Wstawi 2 w miejsce %d
            return false;
        }

        if (mb_strlen($slug) > 100) {
            $this->addError(self::ERROR_TOO_LONG, [100]); // Wstawi 100 w miejsce %d
            return false;
        }

        // Znaki zabronione (spacje, ukośniki)
        if (strpos($slug, '/') !== false || preg_match('/\s/', $slug)) {
            $this->addError(self::ERROR_FORBIDDEN); // Używa: 'Użycie niedozwolonych znaków.'
            return false;
        }

        // Format (regex)
        if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
            $this->addError(self::ERROR_PATTERN); // Używa: 'Niepoprawny format danych.'
            return false;
        }

        return true;
    }

    /**
     * Waliduje pole id_parent - sprawdza czy rodzic istnieje i czy nie tworzy zapętlenia
     *
     * @param array $configuration
     * @return void
     */
    private function validateParentId(array $configuration): void
    {
        $idParentRaw = $configuration['id_parent'] ?? null;
        $idRaw = $configuration['id'] ?? 0;

        // Jeśli puste => kategoria główna, OK
        if (empty($idParentRaw)) {
            return;
        }

        // Sprawdź czy to liczba
        if (!is_numeric($idParentRaw)) {
            $this->addError(self::ERROR_INVALID);
            return;
        }

        $idParent = (int) $idParentRaw;
        $id = (int) $idRaw;

        // Nie może być sam sobie rodzicem
        if ($id !== 0 && $idParent === $id) {
            $this->addError(null, [],'Nie może być sam sobie rodzicem');
            return;
        }

        // Rodzic musi istnieć w bazie
        $parent = $this->repository->find($idParent);
        if ($parent === null) {
            $this->addError(null, [],'Nie znaleziono rodzica.');
            return;
        }

        // Sprawdź zapętlenie: idziemy w górę po rodzicach
        if ($id !== 0) {
            $cursor = $parent;
            $depth = 0;
            $maxDepth = 100; // zabezpieczenie przed nieskończoną pętlą

            while ($cursor !== null && $depth < $maxDepth) {
                if ($cursor->getId() === $id) {
                    $this->addError(null, [],'Nie można ustawić rodzica - utworzyłoby to zapętlenie w drzewie kategorii');
                    return;
                }
                $cursor = $cursor->getParent(); 
                $depth++;
            }
        }
    }
}