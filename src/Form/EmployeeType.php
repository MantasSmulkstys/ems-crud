<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\Employee;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('email')
            ->add('phoneNumber')
            ->add('hireDate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a department',
                'required' => true,
                'attr' => [
                    'class' => 'form-select form-select-lg'
                ],
                'empty_data' => null,
                'preferred_choices' => []
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }
}
