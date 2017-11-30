<?php

namespace AppBundle\Form;


use AppBundle\Entity\JuniorDeveloper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JuniorDeveloperType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text')
            ->add('displayName', 'text')
            ->add('password', 'repeated', array(
                'type' => 'password',
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => JuniorDeveloper::class,
        ));
    }

    public function getName()
    {
        return 'junior_developer';
    }
}