<?php

declare(strict_types=1);

namespace Panda\Blog\Hook\Core;

abstract class AbstractDisplayHook extends AbstractHook 
{
    /**
     * Główna logika wywoływana z hooka.
     */
    public function execute(array $params): string
    {
        if (!$this->shouldBlockBeDisplayed($params)) {
            return '';
        }

        $this->assignTemplateVariables($params);

        return $this->context->smarty->fetch($this->getTemplateFullPath());
    }

    /**
     * Przypisywanie zmiennych do Smarty – domyślnie puste.
     */
    protected function assignTemplateVariables(array $params): void
    {
    }

    /**
     * Czy blok ma być w ogóle wyświetlany.
     * Domyślnie: nie pokazuj w trybie katalogowym.
     */
    protected function shouldBlockBeDisplayed(array $params): bool
    {
        return !\Configuration::isCatalogMode();
    }

    /**
     * Pełna ścieżka szablonu w stylu "module:modul/views/templates/hook/file.tpl".
     */
    public function getTemplateFullPath(): string
    {
        return "module:{$this->module->name}/views/templates/hook/{$this->getTemplate()}";
    }

    /**
     * Zwraca nazwę pliku szablonu, np. "contact_form.tpl".
     */
    abstract protected function getTemplate(): string;
}