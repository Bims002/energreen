<?php

namespace App\Entity;

use App\Repository\LodgmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LodgmentRepository::class)]
class Lodgment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $lodgment_type = null;

    #[ORM\Column(nullable: false)]
    private ?int $surface = null;

    #[ORM\Column(nullable: false)]
    private ?int $occupant = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLodgmentType(): ?string
    {
        return $this->lodgment_type;
    }

    public function setLodgmentType(string $lodgment_type): static
    {
        $this->lodgment_type = $lodgment_type;

        return $this;
    }

    public function getSurface(): ?int
    {
        return $this->surface;
    }

    public function setSurface(int $surface): static
    {
        $this->surface = $surface;

        return $this;
    }

    public function getOccupant(): ?int
    {
        return $this->occupant;
    }

    public function setOccupant(int $occupant): static
    {
        $this->occupant = $occupant;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
