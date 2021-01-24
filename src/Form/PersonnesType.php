<?php

namespace App\Form;

use App\Entity\Adresses;
use App\Entity\Personnes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonnesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sexeType = ["Homme" => "Homme", "Femme" => "Femme"];
        $builder
            ->add('nom')
            ->add('sexe',ChoiceType::class,[
                'choices' => $sexeType,
                'expanded' => true])
            ->add('naissance',DateType::class)
            ->add('adresse',EntityType::class, ['class' => Adresses::class])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Personnes::class,
        ]);
    }
}
