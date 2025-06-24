<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ClientContactAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['label' => 'ID'])
            ->add('name', null, ['label' => 'Имя'])
            ->add('phone', null, ['label' => 'Телефон'])
            ->add('email', null, ['label' => 'Email']);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id', null, ['label' => 'ID'])
            ->add('name', null, ['label' => 'Имя'])
            ->add('phone', null, ['label' => 'Телефон'])
            ->add('email', null, ['label' => 'Email']);
    }
}