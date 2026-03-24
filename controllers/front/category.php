<?php



use Panda\Blog\Entity\PandaBlogCategory;
use Panda\Blog\Presenter\PandaBlogPresenter;
class Panda_blogCategoryModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->presenter = new PandaBlogPresenter();
        $data = $this->findCategoryAndPosts();



        $this->context->smarty->assign(
            [
                'category' => $data['category'],
                'posts' => $data['posts'],
                'breadcrumb' => $this->getBreadcrumbs($data['category'])
            ]

        );
        $this->setTemplate('module:panda_blog/views/templates/front/category.tpl');
    }

    private function findCategoryAndPosts()
    {
        try {
            $categoryRepo = $this->get(Panda\Blog\Repository\PandaBlogCategoryRepository::class);
            $postRepo = $this->get(Panda\Blog\Repository\PandaBlogPostRepository::class);

            $categoryData = $categoryRepo->findBySlug(Tools::getValue('link_rewrite'));
            if (null != $categoryData) {
                $category = $this->presenter->present($categoryData);
                $posts_collection = $postRepo->findActiveByCategory($categoryData);
                if (null != $posts_collection) {
                    $posts = $this->presenter->presentCollection($posts_collection);
                }
            }
        } catch (\Exception $e) {
            Tools::redirect('index.php?controller=404');
            \PrestaShopLoggerCore::addLog('PandaBlog Category page: ' . $e->getMessage(), 3);
            exit;

        }


        return [
            'category' => $category ?? null,
            'posts' => $posts ?? null
        ];
    }


    private function getBreadcrumbs($category)
    {
        $links = [
            [
                'title' => 'Home',
                'url' => _PS_BASE_URL_SSL_
            ]
        ];
        if($category['parent'] != null){
            $links[] = [
                'title' => $category['parent']['name'],
                'url' => $category['parent']['url']
            ];
        }

        $links[] = [
                'title' => $category['name'],
                'url' => $category['url']
            ];
        return [
            'links' => $links,
            'count' => count($links)

        ];
    }




}