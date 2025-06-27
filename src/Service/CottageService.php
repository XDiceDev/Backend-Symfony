<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Cottage;
use Doctrine\ORM\EntityManagerInterface;

class CottageService
{
    private EntityManagerInterface $entityManager;

    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAvailableCottages(): array
    {
        $cottages = $this->entityManager->getRepository(Cottage::class)
            ->findBy(['isAvailable' => true])
        ;

        return array_map(function (Cottage $cottage) {
            return [
                'id' => $cottage->getId(),
                'name' => $cottage->getName(),
                'amenities' => $cottage->getAmenities(),
                'bedCount' => $cottage->getBedCount(),
                'rowFromSea' => $cottage->getRowFromSea(),
            ];
        }, $cottages);
    }
}
