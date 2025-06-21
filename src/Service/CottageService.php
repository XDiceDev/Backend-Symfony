<?php

namespace App\Service;

use App\Entity\Cottage;
use Doctrine\ORM\EntityManagerInterface;

class CottageService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAvailableCottages(): array
    {
        return $this->entityManager->getRepository(Cottage::class)
            ->findBy(['isAvailable' => true]);
    }
}