<?php

declare(strict_types=1);

namespace Panda\Blog\Hook\Core;

use Context;
use Module;
use Panda\Blog\Config\PandaModuleConfig;

abstract class AbstractHook implements HookInterface
{
    /** @var Module */
    protected $module;

    /** @var Context */
    protected $context;

    public function __construct(Module $module, Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    public static function getHookName(): string
    {
        $class = static::class;
        $className = basename(str_replace('\\', '/', $class));

        return lcfirst($className);
    }


  /**
     * Tłumaczenie z użyciem Context Translator.
     *
     * @param string      $message
     * @param array       $params
     * @param string|null $domain  Jeśli null, użyje domyślnej domeny z TranslationDomainProvider
     *
     * @return string
     */
    protected function trans(string $message, array $params = [], ?string $domain = null): string
    {
        if ($domain === null) {
            $domain = (new PandaModuleConfig($this->module))->getTranslationDomain();
        }

        if (method_exists($this->context, 'getTranslator')) {
            return $this->context->getTranslator()->trans($message, $params, $domain);
        }

        return $message;
    }
}