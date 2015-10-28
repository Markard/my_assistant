<?php namespace MyAssistant\AuthJwtBundle\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden', ['mapped' => false])
            ->add('username', 'text', [
                'required' => true,
            ])
            ->add('email', 'email', [
                'required' => true,
            ])
            ->add('password', 'repeated', [
                    'type' => 'password',
                    'invalid_message' => 'The password fields must match.',
                    'required' => true,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => [
                'Default',
                'registration'
            ],
            'csrf_protection' => false,
        ]);
    }
}