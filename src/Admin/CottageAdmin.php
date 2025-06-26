<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CottageAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', null, ['label' => 'Название'])
            ->add('description', null, ['label' => 'Описание'])
            ->add('pricePerNight', null, ['label' => 'Цена за ночь'])
            ->add('address', null, ['label' => 'Адрес'])
            ->add('amenities', EntityType::class, [
                'class' => \App\Entity\Amenity::class,
                'multiple' => true,
                'label' => 'Удобства',
                'by_reference' => false,
            ]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['label' => 'ID'])
            ->add('name', null, ['label' => 'Название'])
            ->add('pricePerNight', null, ['label' => 'Цена за ночь'])
            ->add('address', null, ['label' => 'Адрес'])
            ->add('amenities', null, ['label' => 'Удобства']);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id', null, ['label' => 'ID'])
            ->add('name', null, ['label' => 'Название'])
            ->add('description', null, ['label' => 'Описание'])
            ->add('pricePerNight', null, ['label' => 'Цена за ночь'])
            ->add('address', null, ['label' => 'Адрес'])
            ->add('amenities', null, ['label' => 'Удобства']);
    }
}