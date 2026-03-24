<?php

namespace Panda\Blog\Hook\Core;

interface HookInterface
{
    /**
     * Nazwa hooka, np. "displayContactForm", "displayHeader", itd.
     */
    public static function getHookName(): string;

    /**
     * Logika hooka – odpowiednik hookDisplayXxx($params).
     *
     * @param array $params
     *
     * @return string|null
     */
    public function execute(array $params);
}