<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\Table(name: 'booking')]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $phone;

    #[ORM\ManyToOne(targetEntity: Cottage::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private Cottage $cottage;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $comment = null;

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCottage(): Cottage
    {
        return $this->cottage;
    }

    public function setCottage(Cottage $cottage): self
    {
        $this->cottage = $cottage;

        return $this;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
