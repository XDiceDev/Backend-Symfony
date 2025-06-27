<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Booking;
use App\Entity\Cottage;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class BookingService
{
    private EntityManagerInterface $entityManager;

    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createBooking(string $phone, int $cottageId, string $comment = ''): void
    {
        $cottage = $this->entityManager->getRepository(Cottage::class)->find($cottageId);

        if ($cottage === null) {
            throw new \InvalidArgumentException('Cottage not found');
        }

        $booking = new Booking();
        $booking->setPhone($phone)
            ->setComment($comment)
            ->setCottage($cottage)
        ;

        $this->entityManager->persist($booking);
        $this->entityManager->flush();
    }

    public function updateBookingComment(string $phone, int $cottageId, string $newComment): bool
    {
        $booking = $this->entityManager->getRepository(Booking::class)
            ->findOneBy(['phone' => $phone, 'cottage' => $cottageId])
        ;

        if ($booking === null) {
            return false;
        }

        $booking->setComment($newComment);
        $this->entityManager->flush();

        return true;
    }

    public function deleteBooking(string $phone, int $cottageId): bool
    {
        $booking = $this->entityManager->getRepository(Booking::class)
            ->findOneBy(['phone' => $phone, 'cottage' => $cottageId])
        ;

        if ($booking === null) {
            return false;
        }

        $this->entityManager->remove($booking);
        $this->entityManager->flush();

        return true;
    }
}
