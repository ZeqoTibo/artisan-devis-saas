<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Repository\UserRepository;
use App\State\UserPasswordHasherProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    shortName: 'Utilisateur',
    description: 'Comptes utilisateurs de l\'application.',
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            openapi: new OpenApiOperation(
                security: [['JWT' => []]],
                summary: 'Lister les utilisateurs',
                description: 'Retourne la collection. **Réservé aux administrateurs.**'
            ),
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN')",
            openapi: new OpenApiOperation(
                security: [['JWT' => []]],
                summary: 'Afficher un utilisateur',
                description: 'Retourne un utilisateur. **Réservé aux administrateurs.**'
            ),
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            openapi: new OpenApiOperation(
                security: [['JWT' => []]],
                summary: 'Créer un utilisateur',
                description: 'Crée un utilisateur. **Réservé aux administrateurs.**'
            ),
            processor: UserPasswordHasherProcessor::class,
            validationContext: ['groups' => ['Default', 'user:create']],
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            openapi: new OpenApiOperation(
                security: [['JWT' => []]],
                summary: 'Mettre à jour un utilisateur',
                description: 'Met à jour un utilisateur. **Réservé aux administrateurs.**'
            ),
            processor: UserPasswordHasherProcessor::class,
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            openapi: new OpenApiOperation(
                security: [['JWT' => []]],
                summary: 'Mettre à jour un utilisateur',
                description: 'Met à jour un utilisateur. **Réservé aux administrateurs.**'
            ),
            processor: UserPasswordHasherProcessor::class,
            inputFormats: ['json' => ['application/merge-patch+json']],
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            openapi: new OpenApiOperation(
                security: [['JWT' => []]],
                summary: 'Supprimer un utilisateur',
                description: 'Supprime un utilisateur. **Réservé aux administrateurs.**'
            ),
        ),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN'])]
    #[Groups(['user:read', 'user:write'])]
    private ?string $role = null;

    #[Assert\NotBlank(groups: ['user:create'])]
    #[Groups(['user:write'])]
    private ?string $plainPassword = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Client>
     */
    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: 'user')]
    private Collection $clients;

    /**
     * @var Collection<int, Devis>
     */
    #[ORM\OneToMany(targetEntity: Devis::class, mappedBy: 'user')]
    private Collection $devis;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->devis = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function touchCreatedAt(): void
    {
        $this->role ??= 'ROLE_USER';
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $role = $this->role ?? 'ROLE_USER';

        return \in_array($role, ['ROLE_USER', 'ROLE_ADMIN'], true) ? [$role] : ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
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

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setUser($this);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getUser() === $this) {
                $client->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Devis>
     */
    public function getDevis(): Collection
    {
        return $this->devis;
    }

    public function addDevi(Devis $devi): static
    {
        if (!$this->devis->contains($devi)) {
            $this->devis->add($devi);
            $devi->setUser($this);
        }

        return $this;
    }

    public function removeDevi(Devis $devi): static
    {
        if ($this->devis->removeElement($devi)) {
            // set the owning side to null (unless already changed)
            if ($devi->getUser() === $this) {
                $devi->setUser(null);
            }
        }

        return $this;
    }
}
