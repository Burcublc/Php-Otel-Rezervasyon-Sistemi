<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('image',FileType::class,[
                'label'=>'Otel Resim Galerisi',
                'mapped'=>false,
                'required'=>false,
                'constraints'=> [
                    new File([
                        'maxSize' => '4096k',
                        'mimeTypes' =>[
                            'image/*',
                        ],
                        'mimeTypesMessage'=>'Please upload a valid Image File',
                    ])
                ],
            ])
            ->add('price')
            ->add('numberofroom')
            ->add('status', ChoiceType::class,[
                'choices' => [
                    'Aktif' => 'Aktif',
                    'Pasif' => 'Pasif',
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
