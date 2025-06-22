<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CottageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CottageRepository::class)]
#[ORM\Table(name: 'cottage')]
class Cottage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $bedCount;

    #[ORM\Column(type: 'string', length: 255)]
    private string $amenities;

    #[ORM\Column(type: 'integer')]
    private int $rowFromSea;

    #[ORM\Column(type: 'boolean')]
    private bool $isAvailable = true;

    #[ORM\OneToMany(mappedBy: 'cottage', targetEntity: Booking::class, cascade: ['remove'])]
    private Collection $bookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBedCount(): int
    {
        return $this->bedCount;
    }

    public function setBedCount(int $bedCount): self
    {
        $this->bedCount = $bedCount;

        return $this;
    }

    public function getAmenities(): string
    {
        return $this->amenities;
    }

    public function setAmenities(string $amenities): self
    {
        $this->amenities = $amenities;

        return $this;
    }

    public function getRowFromSea(): int
    {
        return $this->rowFromSea;
    }

    public function setRowFromSea(int $rowFromSea): self
    {
        $this->rowFromSea = $rowFromSea;

        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): self
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setCottage($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            if ($booking->getCottage() === $this) {
                $booking->setCottage(null);
            }
        }

        return $this;
    }
}
