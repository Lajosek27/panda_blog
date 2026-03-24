<?php

namespace Panda\Blog\Controller\Admin;


use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Panda\Blog\Admin\Form\Type\PandaBlogCategoryFormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Panda\Blog\Admin\Grid\Filters\PandaBlogCategoryFilters;
use PrestaShop\PrestaShop\Core\Grid\GridFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Presenter\GridPresenterInterface;
use Symfony\Component\Form\FormError;
use Panda\Blog\Admin\Form\Provider\PandaBlogFormDataProvider;
use Panda\Blog\Repository\PandaBlogCategoryRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Panda\Blog\Admin\Grid\Definition\PandaBlogCategoryGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\GridFilterFormFactoryInterface;
use PrestaShopBundle\Service\Grid\ResponseBuilder;





class PandaBlogCategoryController extends FrameworkBundleAdminController
{
    private $translator;


    public function __construct(
        $translator,
    ) {
        parent::__construct();
        $this->translator = $translator;
    }


    public function list(
        PandaBlogCategoryFilters $filters,
        GridFactoryInterface $categorygGridFactory,
        GridPresenterInterface $gridPresenter,
        int $id_parent = 0
    ) {
        $filters->add([
            'filters' => [
                'id_parent' => $id_parent,
            ],
        ]);

        $grid = $categorygGridFactory->getGrid($filters);

        return $this->render('@Modules/panda_blog/views/templates/admin/category/grid.html.twig', [
            'grid' => $gridPresenter->present($grid),
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
        ]);
        // return $this->render(view: '@Modules/panda_blog/views/templates/admin/test.html.twig');
    }

    private function getToolbarButtons($action = 'list'): array
    {
        switch ($action) {
            case 'edit':
                return [
                    'add' => [
                        'href' => $this->generateUrl('panda_blog_category.list'),
                        'desc' => 'back',
                        'icon' => 'arrow_back',
                        'class' => 'btn-outline-secondary',
                    ],
                ];

            case 'list':
            default:
                return [
                    'add' => [
                        'href' => $this->generateUrl('panda_blog_category.edit', ['id' => 0]),
                        'desc' => 'Dodaj nowy',
                        'icon' => 'add_circle_outline',
                    ],
                ];
        }
    }

    public function edit(
        Request $request,
        PandaBlogFormDataProvider $categorygFormDataProvider
    ) {

        $id_category = (int) $request->attributes->get('id');
        $data = $categorygFormDataProvider->getData();

        $form = $this->createForm(PandaBlogCategoryFormType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $errors = $categorygFormDataProvider->setData($data);

            if (empty($errors)) {
                $this->addFlash('success', $this->translator->trans('Successful update.', [], 'Admin.Notifications.Success'));
                return $this->redirectToRoute('panda_blog_category.list');
            } else {
                $this->addFlash('error', $this->translator->trans('Twój formularz zawiera błędy. Sprawdź pola zaznaczone na czerwono.', [], 'Modules.Pandablog.Admin'));


                $this->addErrorsToForm($errors, $form);
            }
        }

        return $this->render('@Modules/panda_blog/views/templates/admin/form.html.twig', [
            'form' => $form->createView(),
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons('edit'),
            'back_url' => $this->generateUrl('panda_blog_category.list'),
            'edit' => (bool) $id_category,
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
        PandaBlogCategoryRepository $repository
    ) {
        $id = (int) $request->attributes->get('id');

        if ($id <= 0) {
            $this->addFlash('error', 'Nieprawidłowe ID kategorii.');
            return $this->redirectToRoute('panda_blog_category.list');
        }

        try {
            $category = $repository->findById($id);

            if (!$category) {
                $this->addFlash('error', 'Kategoria nie została znaleziona w bazie danych.');
                return $this->redirectToRoute('panda_blog_category.list');
            }

            // Usuwamy kategorię
            $repository->remove($category);

            $this->addFlash('success', 'Kategoria została pomyślnie usunięta.');

        } catch (\Exception $e) {
            // Logujemy błąd dla programisty
            \PrestaShopLoggerCore::addLog(
                'Błąd usuwania kategorii PandaBlog: ' . $e->getMessage(),
                3,
                null,
                'PandaBlogCategory'
            );

            $this->addFlash('error', 'Wystąpił nieoczekiwany błąd podczas usuwania kategorii.');
        }

        return $this->redirectToRoute('panda_blog_category.list');
    }





    public function filter(
        Request $request,
        PandaBlogCategoryGridDefinitionFactory $gridDefinitionFactory,
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

        return $this->redirectToRoute('panda_blog_category.list', $params, Response::HTTP_SEE_OTHER);
    }

}
