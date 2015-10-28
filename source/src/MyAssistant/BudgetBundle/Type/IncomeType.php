<?php namespace MyAssistant\BudgetBundle\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncomeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden', ['mapped' => false])
            ->add('title')
            ->add('price', 'money')
            ->add('date', 'datetime', [
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
        return 'income';
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