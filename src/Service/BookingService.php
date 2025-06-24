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

    public function getBookings(?string $phone = null, ?int $cottageId = null): array
    {
        $qb = $this->entityManager->getRepository(Booking::class)->createQueryBuilder('b');

        if ($phone !== null) {
            $qb->andWhere('b.phone = :phone')->setParameter('phone', $phone);
        }

        if ($cottageId !== null) {
            $qb->andWhere('b.cottage = :cottage')->setParameter('cottage', $this->entityManager->getReference(Cottage::class, $cottageId));
        }

        $bookings = $qb->getQuery()->getResult();

        return array_map(
            fn(Booking $booking) => [
                'id' => $booking->getId(),
                'phone' => $booking->getPhone(),
                'cottageId' => $booking->getCottage()->getId(),
                'comment' => $booking->getComment(),
            ],
            $bookings
        );
    }

    public function createBooking(string $phone, int $cottageId, string $comment): void
    {
        $cottage = $this->entityManager->getRepository(Cottage::class)->find($cottageId);
        if (!$cottage) {
            throw new \Exception('Cottage not found');
        }

        $booking = new Booking();
        $booking->setPhone($phone);
        $booking->setCottage($cottage);
        $booking->setComment($comment);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();
    }

    public function updateBookingComment(string $phone, int $cottageId, string $comment): bool
    {
        $cottage = $this->entityManager->getRepository(Cottage::class)->find($cottageId);
        if (!$cottage) {
            return false;
        }

        $booking = $this->entityManager->getRepository(Booking::class)->findOneBy([
            'phone' => $phone,
            'cottage' => $cottage,
        ]);

        if (!$booking) {
            return false;
        }

        $booking->setComment($comment);
        $this->entityManager->flush();

        return true;
    }

    public function deleteBooking(string $phone, int $cottageId): bool
    {
        $cottage = $this->entityManager->getRepository(Cottage::class)->find($cottageId);
        if (!$cottage) {
            return false;
        }

        $booking = $this->entityManager->getRepository(Booking::class)->findOneBy([
            'phone' => $phone,
            'cottage' => $cottage,
        ]);

        if (!$booking) {
            return false;
        }

        $this->entityManager->remove($booking);
        $this->entityManager->flush();

        return true;
    }
}
