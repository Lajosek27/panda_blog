<?php

declare(strict_types=1);

namespace Panda\Blog\Admin\Form\Data\Configuration;


use PrestaShop\PrestaShop\Core\ConfigurationInterface;


final class PandaBlogConfigurationDataConfiguration extends AbstractPandaDataConfiguration 
{

    private const PANDA_BLOG_BASE_URL = 'PANDA_BLOG_BASE_URL';
    private const PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN = 'PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN';
    private const PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM = 'PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM';

    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {   
        return [
            self::PANDA_BLOG_BASE_URL => $this->configuration->get(self::PANDA_BLOG_BASE_URL),
            self::PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN => $this->configuration->get(self::PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN) ?? false,
            self::PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM => $this->configuration->get(self::PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM) ?? false,
        ];
    }

    public function updateConfiguration(array $configuration): array
    {
        $this->validateConfiguration($configuration);
          
        if (!$this->hasErrors()) {
            $this->configuration->set(
                self::PANDA_BLOG_BASE_URL,
                $configuration[self::PANDA_BLOG_BASE_URL]
            );
            $this->configuration->set(
                self::PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN,
                $configuration[self::PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN]
            );
            $this->configuration->set(
                self::PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM,
                $configuration[self::PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM]
            );
        }

        return $this->getErrors();
    }

    public function validateConfiguration(array $configuration): bool
    {
        // Czyścimy poprzednie błędy
        $this->clearErrors();

        // 1) PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN (checkbox/switch)
        $this->setField(self::PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN);
        if (!array_key_exists(self::PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN, $configuration)) {
            // Brak klucza - traktujemy jako błąd konfiguracji (jeśli pole jest wymagane)
            $this->addError(self::ERROR_EMPTY);
        } else {
            $val = $configuration[self::PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN];
            // Akceptujemy: 1,0,'1','0', true, false
            if (!in_array($val, [0, 1, '0', '1', true, false], true)) {
                $this->addError(self::ERROR_INVALID, [],'Nieprawidłowa wartość (oczekiwano tak/nie).');
            }
        }

        // 2) PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM (checkbox/switch)
        $this->setField(self::PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM);
        if (!array_key_exists(self::PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM, $configuration)) {
            $this->addError(self::ERROR_EMPTY);
        } else {
            $val = $configuration[self::PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM];
            if (!in_array($val, [0, 1, '0', '1', true, false], true)) {
                $this->addError(self::ERROR_INVALID, [],'Nieprawidłowa wartość (oczekiwano tak/nie).');
            }
        }

        // Jeżeli masz jeszcze pole slugowe (tekstowe) - ustaw pole i waliduj jak wcześniej:
        $this->setField(self::PANDA_BLOG_BASE_URL);
        if (!isset($configuration[self::PANDA_BLOG_BASE_URL]) || '' === trim((string) $configuration[self::PANDA_BLOG_BASE_URL])) {
            $this->addError(self::ERROR_EMPTY);
        } else {
            $this->validateLinkRewrite($configuration[self::PANDA_BLOG_BASE_URL]); // ta metoda używa addError() sama
        }

        // Reset currentField (opcjonalne)
        $this->clearField();

        return !$this->hasErrors(); 
    }

    private function validateLinkRewrite($value): bool
    {
        $slug = trim((string) $value);

        if (strpos($slug, '/') !== false || preg_match('/\s/', $slug)) {
            $this->addError(null, [],'Użycie ukośników i spacji jest zabronione.');
            return false;
        }

        if (mb_strlen($slug) > 100) {
            $this->addError(self::ERROR_TOO_LONG, [100]);
            return false;
        }

        if (!preg_match('/^[a-z]+$/', $slug)) {
            $this->addError(self::ERROR_PATTERN, [], 'Dozwolone tylko małe litery a–z.');
            return false;
        } 

        return true;
    }

}