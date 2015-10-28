<?php namespace MyAssistant\BudgetBundle\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PurchaseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden', ['mapped' => false])
            ->add('title')
            ->add('amount', 'integer')
            ->add('price', 'money')
            ->add('boughtAt', 'datetime', [
                'input' => 'datetime',
                'widget' => 'single_text',
                'invalid_message' => 'This value should be date like 2015-01-01.'
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'purchase';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}