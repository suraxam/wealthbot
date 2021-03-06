<?php

namespace Wealthbot\AdminBundle\Form\Type;

use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\Form\Type\AdminProfileType;
use Wealthbot\UserBundle\Form\Type\UserType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreateAdminUserType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var $data \Wealthbot\UserBundle\Entity\User */
        $data = $builder->getData();

        $choices = array('master' => 'Master', 'pm' => 'Manager', 'csr' => 'CSR' );
        $level = null;

        $options['validation_groups'] = array('password');

        if (!is_null($data) && $data->getId()) {
            foreach ($choices as $key => $choice) {
                $role = 'ROLE_ADMIN_'.strtoupper($key);
                if ($data->hasRole($role)) {
                    $level = $key;
                    break;
                }
            }
        }

        $builder
            ->remove('username')
            ->add('profile', new AdminProfileType())
            ->add('level', 'choice', array(
                'choices' => $choices,
                'property_path' => false,
                'preferred_choices' => $level ? array($level) : array()
            ))
        ;

        if (!is_null($data) && $data->getId()) {
            $builder
                ->remove('email')
                ->remove('plainPassword')
                ->add('plainPassword', 'repeated', array(
                    'type' => 'password',
                    'options' => array('translation_domain' => 'FOSUserBundle'),
                    'first_options' => array('label' => 'form.password'),
                    'second_options' => array('label' => 'form.password_confirmation'),
                    'required' => false
                ));
        }


        $builder->addEventListener(FormEvents::BIND, function(FormEvent $event) {
            /** @var $user User */
            $user = $event->getData();
            $form = $event->getForm();

            if (!$user->getId()) {
                $user->setEnabled(true);
            }

            $level = $form->get('level')->getData();

            if (!$form->get('profile')->get('first_name')->getData()) {
                $form->get('profile')->get('first_name')->addError(new FormError('Required.'));
            }

            if (!$form->get('profile')->get('last_name')->getData()) {
                $form->get('profile')->get('last_name')->addError(new FormError('Required.'));
            }

            switch ($level) {
                case 'master':
                    $user->setRoles(array('ROLE_ADMIN_MASTER'));
                    break;
                case 'pm':
                    $user->setRoles(array('ROLE_ADMIN_PM'));
                    break;
                case 'csr':
                    $user->setRoles(array('ROLE_ADMIN_CSR'));
                    break;
            }

            $user->setUsername($user->getEmail());
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'cascade_validation' => true,
            'validation_groups' => function(FormInterface $form) {
                $data = $form->getData();
                if($data->getId()) {
                    return array('password');
                } else {
                    return array('Registration', 'password');
                }
            },
        ));
    }


    public function getName()
    {
        return 'create_admin_user';
    }
}