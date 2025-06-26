<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class AmenityAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', null, ['label' => 'Название'])
            ->add('description', null, ['label' => 'Описание']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['label' => 'ID'])
            ->add('name', null, ['label' => 'Название'])
            ->add('description', null, ['label' => 'Описание']);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id', null, ['label' => 'ID'])
            ->add('name', null, ['label' => 'Название'])
            ->add('description', null, ['label' => 'Описание']);
    }
}