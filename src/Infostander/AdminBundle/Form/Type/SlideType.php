<?php

namespace Infostander\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SlideType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('title', 'text', array('label' => 'slide.add.title', 'translation_domain' => 'InfostanderAdminBundle', 'attr' => array('class' => 'form-control', 'placeholder' => 'slide.add.title')));
    $builder->add('description', 'textarea', array('label' => 'slide.add.description', 'translation_domain' => 'InfostanderAdminBundle', 'attr' => array('rows' => '5', 'class' => 'form-control form-last', 'placeholder' => 'slide.add.description')));
    $builder->add('image', 'file', array('label' => 'slide.add.image'));
    $builder->add('save', 'submit', array('label' => 'slide.add.save', 'translation_domain' => 'InfostanderAdminBundle', 'attr' => array('class' => 'btn btn-lg btn-primary btn-block')));
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Infostander\AdminBundle\Entity\Slide',
    ));
  }

  public function getName()
  {
    return 'slide';
  }
}