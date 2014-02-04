<?php

namespace Infostander\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScreenType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('title', 'text', array('label' => 'screen.add.title', 'translation_domain' => 'InfostanderAdminBundle', 'attr' => array('class' => 'form-control', 'placeholder' => 'screen.add.title')));
    $builder->add('description', 'textarea', array('label' => 'screen.add.description', 'translation_domain' => 'InfostanderAdminBundle', 'attr' => array('rows' => '5', 'class' => 'form-control form-last', 'placeholder' => 'screen.add.description')));
    $builder->add('save', 'submit', array('label' => 'screen.add.save', 'translation_domain' => 'InfostanderAdminBundle', 'attr' => array('class' => 'btn btn-lg btn-primary btn-block')));
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Infostander\AdminBundle\Entity\Screen',
    ));
  }

  public function getName()
  {
    return 'screen';
  }
}