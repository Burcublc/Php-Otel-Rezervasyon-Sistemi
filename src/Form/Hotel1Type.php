<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Hotel;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Hotel1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category',EntityType::class,[
                'class' => Category::class,
                'choice_label' => 'title',
            ])
            ->add('title')
            ->add('keywords')
            ->add('description')
            ->add('image', FileType::class,[
                'label' => 'Otel Anaresmi' ,
                'mapped' => false,
                'required' => false,
            ])
            ->add('star',ChoiceType::class, [
                'choices' => [
                    '1 Star' => '1',
                    '2 Star' => '2',
                    '3 Star' => '3',
                    '4 Star' => '4',
                    '5 Star' => '5',
                ],
            ])


            ->add('address')
            ->add('phone')
            ->add('fax')
            ->add('email')
            ->add('city')
            ->add('country',ChoiceType::class,[
                'choices' => [
                    'Türkiye' => 'Türkiye',
                    'Güney Kore' => 'Güney Kore',
                    'Amerika Birlesik Devletleri' =>'Amerika Birlesik Devletleri',
                    'Japonya' => 'Japonya',
                    'Cin' => 'Cin',
                    'Fransa' => 'Fransa',
                    'İngiltere' => 'ingiltere',
                ],
            ])
            ->add('location',ChoiceType::class,[
                'choices' => [
                    'Istanbul' => 'Istanbul',
                    'Ankara' => 'Ankara',
                    'Mus' => 'Mus',
                    'Izmir' => 'Izmir',
                    'Newyork' => 'Newyork',
                    'Tokyo' => 'Tokyo',
                    'Londra' => 'Londra',
                    'Paris' => 'Paris',
                    'Pekin' => 'Pekin',
                    'Washington' => 'Washington',
                ],
            ])
            ->add('detail',CKEditorType::class, array(
                'config' => array(
                    'uiColor' => '#ffffff',
                    //...
                ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class,
        ]);
    }
}
