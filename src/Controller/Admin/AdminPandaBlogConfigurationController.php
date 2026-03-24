<?php

namespace Panda\Blog\Controller\Admin;

use Panda\Blog\Admin\Form\Type\PandaBlogConfigurationFormType;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\FormError;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;


class AdminPandaBlogConfigurationController extends FrameworkBundleAdminController
{
    private $formFactory;
    private $dataProvider;
    private $translator;

    public function __construct(
        FormFactoryInterface $formFactory,
        FormDataProviderInterface $dataProvider,
        TranslatorInterface $translator
    ) {
        $this->formFactory = $formFactory;
        $this->dataProvider = $dataProvider;
        $this->translator = $translator;
    }


    public function indexAction(Request $request)
    {
        $data = $this->dataProvider->getData();
        $form = $this->formFactory->create(PandaBlogConfigurationFormType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $this->dataProvider->setData($form->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->translator->trans('Successful update.', [], 'Admin.Notifications.Success'));
                return $this->redirectToRoute('panda_blog_configuration');
            } else {
                $this->addFlash('error', $this->translator->trans('Twój formularz zawiera błędy. Sprawdź pola zaznaczone na czerwono.', [], 'Modules.Pandablog.Admin'));


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
        }

        return $this->render('@Modules/panda_blog/views/templates/admin/configuration.html.twig', [
            'configurationForm' => $form->createView(),
        ]);
    }
}