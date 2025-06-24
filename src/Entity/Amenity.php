<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AmenityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Cottage;

#[ORM\Entity(repositoryClass: AmenityRepository::class)]
class Amenity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Cottage::class, mappedBy: 'amenities')]
    private Collection $cottages;

    public function __construct()
    {
        $this->cottages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCottages(): Collection
    {
        return $this->cottages;
    }

    public function addCottage(Cottage $cottage): void
    {
        if (!$this->cottages->contains($cottage)) {
            $this->cottages->add($cottage);
        }
    }

    public function removeCottage(Cottage $cottage): void
    {
        $this->cottages->removeElement($cottage);
    }
}
