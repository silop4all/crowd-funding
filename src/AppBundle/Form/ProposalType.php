<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserType
 *
 * @author Panagiotis Minos
 */
// src/AppBundle/Form/UserType.php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
class ProposalType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('title', TextType::class, array('label' => 'Proposal Name:',
                    'attr' => array(
                        'class' => 'form-control', 'placeholder' => 'Enter proposal name',
                    )
                ))
                ->add('summary', TextareaType::class, array('label' => 'Proposal Summary:',
                    'attr' => array(
                        'class' => 'form-control', 'placeholder' => 'Enter a short description',
                    )
                ))
                ->add('content', CKEditorType::class, array('label' => 'Content:',
                    'attr' => array(
                        'class' => 'form-control ckeditor', 'placeholder' => 'Content',
                    ),
                    'config' => array(
                        'filebrowserBrowseRoute' => 'elfinder',
                        'filebrowserBrowseRouteParameters' => array(
                            'instance' => 'default',
                            'homeFolder' => ''
                        )
                    ),
                ))
                ->add('amount', TextType::class, array('label' => 'Amount:',
                    'attr' => array(
                        'class' => 'form-control', 'placeholder' => 'Amount',
                    )))

        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Proposal',
        ));
    }

}
