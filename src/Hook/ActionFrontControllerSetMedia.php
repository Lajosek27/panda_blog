<?php

declare(strict_types=1);

namespace Panda\Blog\Hook;

use Panda\Blog\Hook\Core\AbstractHook;



class ActionFrontControllerSetMedia extends AbstractHook {
   
  
    public function execute(array $params){
        $this->context->controller->registerJavascript(
            "module-{$this->module->name}-swiper-js",
            "modules/{$this->module->name}/views/js/test.js",
            [
            'position' => 'bottom',
            'priority' => 200,
            ]
    )   ;
        $this->context->controller->registerStylesheet(
            "module-{$this->module->name}-blog",
            "modules/{$this->module->name}/views/css/blog.css",
            [
              'media' => 'all',
              'priority' => 200,
            ]
        );
    }
}