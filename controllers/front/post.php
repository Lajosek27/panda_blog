<?php



use Panda\Blog\Entity\PandaBlogPost;
use Panda\Blog\Presenter\PandaBlogPresenter;
class Panda_blogPostModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->presenter = new PandaBlogPresenter();
        
        $data = $this->findCategoryAndPost();

        if ($data['category'] !== null && $data['post'] !== null) {

            $blog_configuration = PandaBlogPresenter::getBlogConfiguration();
            $this->context->smarty->assign($blog_configuration);
            if($blog_configuration && 
                ($blog_configuration['PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM'] || $blog_configuration['PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN'] )
            ){

            
                $this->context->smarty->assign(
                    [
                        'category_posts' => $this->getCategoryPosts($data['category'],$data['post']),

                    ]

                );
            } 
        }else{
            header('Location: ' . $this->context->link->getPageLink('404'));
            exit;
        }
        $products = $this->getRelatedProducts($data['post']);
       
        $this->context->smarty->assign(
            [
                'category' => $this->presenter->present($data['category']),
                'post' => $this->presenter->present($data['post']),
                'breadcrumb' => $this->getBreadcrumbs($this->presenter->present($data['post'])),
                'products' => $products
            ]

        );
        
        $this->setTemplate('module:panda_blog/views/templates/front/post.tpl');
    }

    private function getRelatedProducts(?PandaBlogPost $post){
        if(null == $post){
            return [];
        }

        $ids = $post->getRelatedProductIds();
        if(empty($ids))
            return [];

        return $this->presenter->presentProducts($ids) ?? false;
    }

    private function findCategoryAndPost()
    {
        try {
            $categoryRepo = $this->get(Panda\Blog\Repository\PandaBlogCategoryRepository::class);
            $postRepo = $this->get(Panda\Blog\Repository\PandaBlogPostRepository::class);

            $category = $categoryRepo->findBySlug(Tools::getValue('category_slug'));
            if (null != $category) {
                
                $post = $postRepo->findBySlug(Tools::getValue('post_slug'));
            }
        } catch (\Exception $e) {
            Tools::redirect('index.php?controller=404');
            \PrestaShopLoggerCore::addLog('PandaBlog Post page: ' . $e->getMessage(), 3);
            exit;

        }


        return [
            'category' => $category ?? null,
            'post' => $post ?? null
        ];
    }
    private function getCategoryPosts($category,$post)
    {
        try {
            $postRepo = $this->get(Panda\Blog\Repository\PandaBlogPostRepository::class);

            $posts_data = $postRepo->findActiveByCategory($category,[$post],5);
            if (null != $posts_data) {
                $posts = $this->presenter->presentCollection($posts_data);
            }
           
        } catch (\Exception $e) {
            Tools::redirect('index.php?controller=404');
            \PrestaShopLoggerCore::addLog('PandaBlog Post page: ' . $e->getMessage(), 3);
            exit;

        }


        return $posts ?? null;
    }

     private function getBreadcrumbs($post)
    {   
       
        $links = [
            [
                'title' => 'Home',
                'url' => _PS_BASE_URL_SSL_
            ]
        ];
        if($post['main_category'] != null){
            $links[] = [
                'title' => $post['main_category']['name'],
                'url' => $post['main_category']['url']
            ];
        }

        $links[] = [
                'title' => $post['title'],
                'url' => $post['url']
            ];

        return [
            'links' => $links,
            'count' => count($links)

        ];
    }
}