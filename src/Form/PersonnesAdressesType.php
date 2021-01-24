<?php

namespace App\Form;

use App\Entity\Adresses;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonnesAdressesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sexeType = ["Homme" => "Homme", "Femme" => "Femme"];
        $builder
            ->add('nom', TextType::class, ['required' => false])
            ->add('sexe', ChoiceType::class,[
                'choices' => $sexeType,
                'expanded' => true,
                'label' => "Sexe",
            ])
            ->add('naissance', DateType::class,[
                'widget' => 'single_text',
                // prevents rendering it as type="date", to avoid HTML5 date pickers
                'html5' => false,
                // adds a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
                'required' => false,
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
            // Configure your form options here
        ]);
    }
}
