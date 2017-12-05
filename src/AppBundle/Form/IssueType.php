<?php
/**
 * Created by PhpStorm.
 * User: fischer.adam
 * Date: 2017.12.01.
 * Time: 15:14
 */

namespace AppBundle\Form;


use AppBundle\Entity\JiraIssue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IssueType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            "data_class" => JiraIssue::class
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('taskNumber', 'text', [
                'label' => 'Task száma',
                'property_path' => 'taskNumber'
            ])
            ->add('developer', 'text', [
                'label' => 'Fejlesztő neve',
            ])
            ->add('save', 'submit')
        ;
    }

    public function getName()
    {
        return 'issue_form';
    }
}