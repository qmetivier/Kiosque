<?php

namespace CONSERTO\KiosqueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class magazineType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
          ->add('Title',    TextType::class)
          ->add('content',  TextareaType::class)
          ->add('pdf',      PdfType::class)
          ->add('categories', EntityType::class, array(
            'class'        => 'CONSERTOKiosqueBundle:Category',
            'choice_label' => 'name',
            'multiple'     => true,
            'expanded'     => true,
          ))
          ->add('Sauvegarder',       SubmitType::class)
      ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CONSERTO\KiosqueBundle\Entity\magazine'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'conserto_kiosquebundle_magazine';
    }


}
