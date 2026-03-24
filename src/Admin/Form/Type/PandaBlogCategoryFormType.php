<?php
declare(strict_types=1);

namespace Panda\Blog\Admin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;

class PandaBlogCategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_category', HiddenType::class, [
                'required' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'Nazwa kategorii',
                'required' => true,
            ])
            ->add('slug', TextType::class, [
                'label' => 'Przyjazny link',
                'required' => true,
                'help' => 'Dozwolone: a-z, 0-9, oraz znaki - i _. Bez spacji i polskich znaków.'
            ])
            ->add('is_active', SwitchType::class, [
                'label' => 'Aktywna',
                'required' => false,
                'help' => 'Czy kategoria ma być widoczna na stronie',
            ])
            //TODO:zamienić na ładne drzewko wyboru kategorii
            ->add('id_parent', IntegerType::class, [
                'label' => 'Id rodzica',
                'required' => false,
                'help' => 'Zostaw puste jeśli to kategoria główna'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis kategorii',
                'required' => false,
            ])
            ->add('meta_title', TextType::class, [
                'label' => 'Meta tutuł',
                'required' => false,
                'help' => 'Jeśli puste użyjemy głównego tytułu'
            ])
            ->add('meta_description', TextareaType::class, [
                'label' => 'Opis kategorii',
                'required' => false,
                'help' => 'Jeśli puste użyjemy głównego opisu'
            ])

        ;
    }
}