<?php

namespace Panda\Blog\Controller\Admin;


use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Panda\Blog\Admin\Form\Type\PandaBlogPostFormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Panda\Blog\Admin\Grid\Filters\PandaBlogPostFilters;
use PrestaShop\PrestaShop\Core\Grid\GridFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Presenter\GridPresenterInterface;
use Symfony\Component\Form\FormError;
use Panda\Blog\Admin\Form\Provider\PandaBlogFormDataProvider;
use Panda\Blog\Repository\PandaBlogPostRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Panda\Blog\Admin\Grid\Definition\PandaBlogPostGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\GridFilterFormFactoryInterface;
use PrestaShopBundle\Service\Grid\ResponseBuilder;





class PandaBlogPostController extends FrameworkBundleAdminController
{
    private $translator;


    public function __construct(
        $translator,
    ) {
        parent::__construct();
        $this->translator = $translator;
    }


    public function list(
        PandaBlogPostFilters $filters,
        GridFactoryInterface $postGridFactory,
        GridPresenterInterface $gridPresenter,
        int $id_parent = 0
    ) {

        $grid = $postGridFactory->getGrid($filters);

        return $this->render('@Modules/panda_blog/views/templates/admin/post/grid.html.twig', [
            'grid' => $gridPresenter->present($grid),
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
        ]);
        return $this->render(view: '@Modules/panda_blog/views/templates/admin/test.html.twig');
    }

    private function getToolbarButtons($action = 'list'): array
    {
        switch ($action) {
            case 'edit':
                return [
                    'add' => [
                        'href' => $this->generateUrl('panda_blog_post.list'),
                        'desc' => 'Wróć do listy',
                        'icon' => 'arrow_back',
                        'class' => 'btn-outline-secondary',
                    ],
                ];

            case 'list':
            default:
                return [
                    'add' => [
                        'href' => $this->generateUrl('panda_blog_post.edit', ['id' => 0]),
                        'desc' => 'Dodaj nowy',
                        'icon' => 'add_circle_outline',
                    ],
                ];
        }
    }

    public function edit(
        Request $request,
        PandaBlogFormDataProvider $postFormDataProvider
    ) {

        $post_id = (int) $request->attributes->get('id');
        $data = $postFormDataProvider->getData();

        $form = $this->createForm(PandaBlogPostFormType::class, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $formData = $request->request->all();
               
            $mainCatId = $formData['form']['panda_blog_post_form_main_category_id']['tree'] ?? null;
        
            $data = $form->getData();
            $data['main_category_id'] = $mainCatId;

            if ($form->isValid()) {
                $errors = $postFormDataProvider->setData($data);


                if (empty($errors)) {
                    $this->addFlash('success', $this->translator->trans('Successful update.', [], 'Admin.Notifications.Success'));
                    return $this->redirectToRoute('panda_blog_post.list');
                } else {
                    $this->addFlash('error', $this->translator->trans('Twój formularz zawiera błędy. Sprawdź pola zaznaczone na czerwono.', [], 'Modules.Pandablog.Admin'));


                    $this->addErrorsToForm($errors, $form);
                }
            }

        }

        return $this->render('@Modules/panda_blog/views/templates/admin/form.html.twig', [
            'form' => $form->createView(),
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons('edit'),
            'back_url' => $this->generateUrl('panda_blog_post.list'),
            'edit' => (bool) $post_id,
        ]);

    }

    private function addErrorsToForm($errors, $form)
    {
        // Dodaj błędy do pól formularza
        foreach ($errors as $fieldName => $fieldErrors) {
            if ($fieldName === '_global') {
                // Błędy globalne dodajemy do całego formularza
                foreach ($fieldErrors as $errorMessage) {
                    $form->addError(new FormError($errorMessage));
                }
            } else {
                // Błędy do konkretnego pola
                if ($form->has($fieldName)) {
                    $field = $form->get($fieldName);
                    foreach ($fieldErrors as $errorMessage) {
                        $field->addError(new FormError($errorMessage));
                    }
                } else {
                    // Jeśli pole nie istnieje, dodaj błąd globalny
                    foreach ($fieldErrors as $errorMessage) {
                        $form->addError(new FormError($errorMessage));
                    }
                }
            }
        }
    }





    public function delete(
        Request $request,
        PandaBlogPostRepository $repository
    ) {
        $id = (int) $request->attributes->get('id');

        if ($id <= 0) {
            $this->addFlash('error', 'Nieprawidłowe ID Wpisu.');
            return $this->redirectToRoute('panda_blog_post.list');
        }

        try {
            $post = $repository->findOneById($id);

            if (!$post) {
                $this->addFlash('error', 'Kategoria nie została znaleziona w bazie danych.');
                return $this->redirectToRoute('panda_blog_post.list');
            }

            
            $repository->remove($post);

            $this->addFlash('success', 'Kategoria została pomyślnie usunięta.');

        } catch (\Exception $e) {
            // Logujemy błąd dla programisty
            \PrestaShopLoggerCore::addLog(
                'Błąd usuwania Wpisu PandaBlog: ' . $e->getMessage(),
                3,
                null,
                'PandaBlogPost'
            );

            $this->addFlash('error', 'Wystąpił nieoczekiwany błąd podczas usuwania Wpisu.');
        }

        return $this->redirectToRoute('panda_blog_post.list');
    }


    public function toggleActive(
        Request $request,
        PandaBlogPostRepository $repository
    ) {
        $id = (int) $request->attributes->get('id');

        if ($id <= 0) {
            $this->addFlash('error', 'Nieprawidłowe ID wpisu.');
            return $this->redirectToRoute('panda_blog_post.list');
        }

        try {
            $post = $repository->findOneById($id);

            if (!$post) {
                $this->addFlash('error', 'Wpis nie została znaleziona w bazie danych.');
                return $this->redirectToRoute('panda_blog_post.list');
            }

            $active = $post->getIsActive();
            $post->setIsActive(!$active);
            $repository->save($post);

            $this->addFlash('success', 'Wpis został ' . !$active ? "wyłączony" : "włączony" );

        } catch (\Exception $e) {
            // Logujemy błąd dla programisty
            \PrestaShopLoggerCore::addLog(
                'Błąd usuwania Wpisu PandaBlog: ' . $e->getMessage(),
                3,
                null,
                'PandaBlogPost'
            );

            $this->addFlash('error', 'Wystąpił nieoczekiwany błąd podczas usuwania Wpisu.');
        }

        return $this->redirectToRoute('panda_blog_post.list');
    }




    public function filter(
        Request $request,
        PandaBlogPostGridDefinitionFactory $gridDefinitionFactory,
        GridFilterFormFactoryInterface $filterFormFactory
    ): RedirectResponse {

        $definition = $gridDefinitionFactory->getDefinition();
        $filtersForm = $filterFormFactory->create($definition)->handleRequest($request);
        $params = [];

        if ($filtersForm->isSubmitted()) {
            $params = [
                $gridDefinitionFactory->getFilterId() => [
                    'filters' => $filtersForm->getData(),
                ],

            ];
        }

        return $this->redirectToRoute('panda_blog_post.list', $params, Response::HTTP_SEE_OTHER);
    }

}
