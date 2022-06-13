<?php

namespace SYJS\JsBundle\Tests\App\Form;

use SYJS\JsBundle\Tests\App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserInscription extends AbstractType
{
    // @TODO $options["style"]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('email')
            ->add('error', TextType::class)
        ;
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'extra_fields' => ['error'],
            'style' => 'style',
        ]);
    }

    public function getName()
    {
        $function = new \ReflectionClass($this);

        return $function->getShortName();
    }
}
