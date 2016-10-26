<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Form\ReponseType;

class QuestionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text')
            //->add('ip')
            //->add('date', DateTimeType::class)
            ->add('mail')
            ->add('username')
            //->add('valided')
            //->add('reponse')
            ->add('dead')
            ->add('reponses', CollectionType::class,
                array(
                    // each entry in the array will be an "email" field
                    'entry_type'   => ReponseType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => true,
                    // these options are passed to each "email" type
                    'entry_options'  => array(
                        //'attr'      => array('class' => 'email-box')
                    ),
                ))


            //->add('submit', SubmitType::class)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Question'
        ));
    }
}
