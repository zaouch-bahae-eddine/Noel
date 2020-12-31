<?php

namespace App\Form;

use App\Entity\Adresses;
use App\Entity\Personnes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonnesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sexeType = ["Home" => "Home", "Femme" => "Femme"];
        $builder
            ->add('nom', TextType::class)
            ->add('sexe', ChoiceType::class,[
                'choices' => $sexeType,
                'expanded' => true,
                'label' => "Sexe"
            ])
            ->add('naissance', DateType::class,[
                'widget' => 'choice',
            ])
            ->add('adresse', EntityType::class, [
                'class' => Adresses::class,
                'required' => false,
            ])
            ->add('nouvelleAdresse', AdressesType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Personnes::class,
        ]);
    }
}
