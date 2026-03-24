<?php

declare(strict_types=1);

namespace Panda\Blog\Hook;


use Panda\Blog\Hook\Core\HookInterface;



class ModuleRoutes implements HookInterface
{


    public function execute(array $params): array
    {   
        $baseUrl = (string) \ConfigurationCore::get('PANDA_BLOG_BASE_URL');
       
        $routes = [
            'panda-blog-category' => [
                'controller' => 'category',
                'rule' => "$baseUrl/{link_rewrite}",
                'keywords' => [
                    'link_rewrite' => [
                        'regexp' => '[_a-zA-Z0-9-\pL]+',
                        'param' => 'link_rewrite'
                        ],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'panda_blog',
                ],
            ],
            
            'panda-blog-post' => [
                'controller' => 'post',
                'rule' => "$baseUrl/{category_slug}/{post_slug}",
                'keywords' => [
                    'category_slug' => [
                        'regexp' => '[_a-zA-Z0-9-\pL]+',
                        'param' => 'category_slug'
                        ],
                    'post_slug' => [
                        'regexp' => '[_a-zA-Z0-9-\pL]+',
                        'param' => 'post_slug'
                        ],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'panda_blog',
                ],
            ],
           
        ];

        return $routes;
    }

    public static function getHookName(): string
    {
        $class = static::class;
        $className = basename(str_replace('\\', '/', $class));

        return lcfirst($className);
    }
}