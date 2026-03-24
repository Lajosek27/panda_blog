<?php

declare(strict_types=1);

namespace Panda\Blog\Hook;

use Panda\Blog\Hook\Core\AbstractDisplayHook;
use Panda\Blog\Presenter\PandaBlogPresenter;
use Panda\Blog\Repository\PandaBlogPostRepository;


class DisplayPandaBlog extends AbstractDisplayHook {
   
    protected $template = "displayPandaBlog.tpl";

    /** @var PandaBlogPostRepository */
    protected $repository;

     public function __construct(\Module $module, \Context $context, PandaBlogPostRepository $repository)
    {
        $this->module = $module;
        $this->context = $context;
        $this->repository = $repository;
    }
    protected function getTemplate(): string{
        return $this->template;
    }


    protected function shouldBlockBeDisplayed(array $params): bool
    {
        return true;
        // return count($this->provider->getPosts()) > 3;
    }

    protected function assignTemplateVariables(array $params): void
    {   
        
        $posts = $this->repository->findLatest(3);
        $presentedPosts = (new PandaBlogPresenter())->presentCollection($posts);

        $this->context->smarty->assign(
            [
                'posts' => $presentedPosts
            ]
        );
    }
}