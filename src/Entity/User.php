<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cet email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> Les rôles de l'utilisateur
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * @var string Le mot de passe haché
     */
    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $statut_pro = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Lodgment $lodgment = null;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: BilanCarbone::class)]
    private Collection $bilansCarbone;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->bilansCarbone = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getStatutPro(): ?string
    {
        return $this->statut_pro;
    }

    public function setStatutPro(string $statut_pro): static
    {
        $this->statut_pro = $statut_pro;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getLodgment(): ?Lodgment
    {
        return $this->lodgment;
    }

    public function setLodgment(?Lodgment $lodgment): static
    {
        if ($lodgment !== null && $lodgment->getUser() !== $this) {
            $lodgment->setUser($this);
        }

        $this->lodgment = $lodgment;
        return $this;
    }

    /**
     * @return Collection<int, BilanCarbone>
     */
    public function getBilansCarbone(): Collection
    {
        return $this->bilansCarbone;
    }

    public function addBilanCarbone(BilanCarbone $bilanCarbone): static
    {
        if (!$this->bilansCarbone->contains($bilanCarbone)) {
            $this->bilansCarbone->add($bilanCarbone);
            $bilanCarbone->setUtilisateur($this);
        }

        return $this;
    }

    public function removeBilanCarbone(BilanCarbone $bilanCarbone): static
    {
        if ($this->bilansCarbone->removeElement($bilanCarbone)) {
            if ($bilanCarbone->getUtilisateur() === $this) {
                $bilanCarbone->setUtilisateur(null);
            }
        }

        return $this;
    }
}