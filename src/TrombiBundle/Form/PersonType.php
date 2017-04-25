<?php

namespace TrombiBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sex', ChoiceType::class, [
                'choices'=> ['Male'=>'M', 'Female'=>'F']
            ])
            ->add('firstname')
            ->add('lastname')
            ->add('birthdate', BirthdayType::class)
            ->add('description')
            ->add('category', EntityType::class, [
                'class'=>'TrombiBundle:Category',
                'choice_label'=>'name',
                'expanded'=>true,
                'multiple'=>false
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TrombiBundle\Entity\Person'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'trombibundle_person';
    }


}
