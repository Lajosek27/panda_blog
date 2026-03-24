<?php


declare(strict_types=1);

namespace Panda\Blog\Config;

use PrestaShopExceptionCore;


abstract class AbstractModuleConfig
{
    protected $service_domain;
    protected $domain;
    protected array $hooks = [];
    protected array $installSqls = [];
    protected array $uninstallSqls = []; //def empty to not delete datebase
    protected string $prefix = _DB_PREFIX_;

    protected \Module $module;

    public function __construct(
        \Module $module
    ) {
        $this->module = $module;
    }
    public function install(): bool
    {
        return $this->installHooks() && $this->installDatabase();
    }

    public function uninstall(): bool
    {
        return $this->uninstallDatabase();
    }

    private function installHooks(): bool
    {
        $success = true;

        foreach ($this->hooks as $hook) {
            if (!$this->module->registerHook($hook)) {
                $success = false;
            }
        }

        return $success;
    }

    private function installDatabase(): bool
    {   
        $db = \Db::getInstance();

        $replacements = $this->getDatabaseInfo();

        foreach ($this->installSqls as $query) {
            $sql = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $query
            );

            if (!$db->execute($sql)) {
                throw new PrestaShopExceptionCore($db->getMsgError());

            }
        }
        return true;
    }

    private function uninstallDatabase(): bool
    {
        $success = true;

        foreach ($this->uninstallSqls as $query) {
            if (!\Db::getInstance()->execute($query)) {
                
                $success = false;
            }
        }

        return $success;
    }

    public function getDatabaseInfo(): array
    {
        return [
            '_PREFIX_' => $this->prefix,
            '_ENGINE_' => _MYSQL_ENGINE_,
            '_CHARSET_' => 'utf8mb4',
        ];
    }


    public function getHooks(): array
    {
        return $this->hooks;
    }
    public function getInstallSqls(): array
    {
        return $this->installSqls;
    }
    public function getUninstallSqls(): array
    {
        return $this->uninstallSqls;
    }
    public function getServiceDomain(): string
    {
        return $this->service_domain;
    }
    public function getTranslationDomain(): string
    {
        return $this->domain;
    }

}
