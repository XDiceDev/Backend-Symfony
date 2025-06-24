<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class BookingAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('phone', null, ['label' => 'Телефон'])
            ->add('cottage', EntityType::class, [
                'class' => \App\Entity\Cottage::class,
                'label' => 'Коттедж',
            ])
            ->add('comment', null, ['label' => 'Комментарий']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['label' => 'ID'])
            ->add('phone', null, ['label' => 'Телефон'])
            ->add('cottage', null, ['label' => 'Коттедж'])
            ->add('comment', null, ['label' => 'Комментарий']);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id', null, ['label' => 'ID'])
            ->add('phone', null, ['label' => 'Телефон'])
            ->add('cottage', null, ['label' => 'Коттедж'])
            ->add('comment', null, ['label' => 'Комментарий']);
    }
}