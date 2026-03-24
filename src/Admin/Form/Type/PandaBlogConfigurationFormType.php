<?php
declare(strict_types=1);

namespace Panda\Blog\Admin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use PrestaShopBundle\Form\Admin\Type\SwitchType;

class PandaBlogConfigurationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('PANDA_BLOG_BASE_URL', TextType::class, [
                'label' => 'Domyślny link do blog',
                'required' => true,
                'help' => 'Wpisz końcówkę adresu (np. "blog"), aby uzyskać link: domena.pl/blog',
                'attr' => [
                    'placeholder' => 'np. blog'
                ]
            ])
            ->add('PANDA_BLOG_SAMECATEGORY_POSTS_COLUMN', SwitchType::class, [
                'choices' => [
                    'Nie' => 0,
                    'Tak' => 1,
                ],
                'label' => 'Pokaż lewą kolumnę z podobnymi wpisami',
                'required' => false,
                'help' => 'Włącz, aby wyświetlać lewą kolumnę z podobnymi wpisami.',
            ])
            ->add('PANDA_BLOG_SAMECATEGORY_POSTS_BOTTOM', SwitchType::class, [
                'choices' => [
                    'Nie' => 0,
                    'Tak' => 1,
                ],
                'label' => 'Pokaż podobne posty poniżej',
                'required' => false,
                'help' => 'Włącz, aby wyświetlać podobne posty poniżej wpisu',
            ])
        ;
    }
}