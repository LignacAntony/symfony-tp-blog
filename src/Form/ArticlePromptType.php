<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticlePromptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prompt', TextareaType::class, [
                'label' => 'Décrivez le sujet de votre article',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Par exemple : Je voudrais un article sur les bienfaits de la méditation pour la santé mentale...',
                    'class' => 'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md'
                ],
                'help' => 'Soyez aussi précis que possible dans votre description pour obtenir un meilleur résultat.'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
} 