<?php

namespace Netrunnerdb\CardsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CycleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('name')
            ->add('nameFr')
            ->add('nameDe')
            ->add('nameEs')
            ->add('namePl')
            ->add('number')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Netrunnerdb\CardsBundle\Entity\Cycle'
        ));
    }

    public function getName()
    {
        return 'netrunnerdb_cardsbundle_cycletype';
    }
}
