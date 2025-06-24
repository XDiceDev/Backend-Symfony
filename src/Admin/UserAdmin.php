<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAdmin extends AbstractAdmin
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(string $code, string $class, string $baseControllerName, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->passwordHasher = $passwordHasher;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('email', null, ['label' => 'Email'])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Пользователь' => 'ROLE_USER',
                    'Администратор' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'label' => 'Роли',
            ])
            ->add('password', null, ['label' => 'Пароль', 'required' => false]);
    }

    protected function prePersist(object $object): void
    {
        if ($object->getPassword()) {
            $object->setPassword($this->passwordHasher->hashPassword($object, $object->getPassword()));
        }
    }

    protected function preUpdate(object $object): void
    {
        if ($object->getPassword()) {
            $object->setPassword($this->passwordHasher->hashPassword($object, $object->getPassword()));
        }
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['label' => 'ID'])
            ->add('email', null, ['label' => 'Email'])
            ->add('roles', null, ['label' => 'Роли']);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id', null, ['label' => 'ID'])
            ->add('email', null, ['label' => 'Email'])
            ->add('roles', null, ['label' => 'Роли']);
    }
}