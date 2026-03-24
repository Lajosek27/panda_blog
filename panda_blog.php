<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use Panda\Blog\Config\PandaModuleConfig;
use Panda\Blog\Hook\Core\HookInterface;

class Panda_Blog extends Module
{
    public function __construct()
    {
        $this->name = 'panda_blog';
        $this->version = '2.0.0';
        $this->author = 'Panda Coders';
        $this->tab = 'others';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Panda Blog');
        $this->description = $this->l('Dodaje Blog');


        
    }



    private function getModuleConfig(): PandaModuleConfig
    {
        return new PandaModuleConfig($this);
    }
    /**
     * @return bool
     */
    public function install(): bool
    {
        return parent::install() && $this->getModuleConfig()->install();
    }

    /**
     * @return bool
     */
    public function uninstall(): bool
    {
        return parent::uninstall();
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /** @param string $methodName */
    public function __call($methodName, array $arguments)
    {   

        
        if (str_starts_with($methodName, 'hook')) {
            if ($hook = $this->getHookObject($methodName)) {
                $params = $arguments[0] ?? [];
                
                return $hook->execute($params);
            }
        }

        return null;
    }

    /**
     * @param string $methodName
     *
     * @return HookInterface|null
     */
    private function getHookObject($methodName)
    {   
        // hookDisplayContactForm -> display_contact_form
        $hookNamePart = \Tools::toUnderscoreCase(str_replace('hook', '', $methodName));
        $serviceName = sprintf(
            $this->getModuleConfig()->getServiceDomain() . '.hook.%s',
            $hookNamePart
        );
      
        $hook = $this->getService($serviceName);
        
        return $hook instanceof HookInterface ? $hook : null;
    }

    /**
     * @template T
     *
     * @param class-string<T>|string $serviceName
     *
     * @return T|object|null
     */
    public function getService($serviceName)
    {
        try {
            if($serviceName === 'panda_blog.hook.module_routes'){
                return new Panda\Blog\Hook\ModuleRoutes();
            }
            return $this->get($serviceName);
        } catch (\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $exception) {
            return null;
        }
    }



}