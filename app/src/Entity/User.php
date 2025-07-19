<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
class User extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ORM\Column(type: 'guid')]
    private string $guid;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 6, nullable: true)]
    private ?string $confirmationCode = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isConfirmed = false;

    #[ORM\Column(nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $resetTokenExpiresAt = null;

    #[ORM\OneToMany(targetEntity: Cart::class, mappedBy: 'user')]
    private Collection $carts;

    public function __construct()
    {
        parent::__construct();

        $this->carts = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function setGuid(string $guid): void
    {
        $this->guid = $guid;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullNameOrEmail(): string
    {
        $firstName = trim((string) $this->firstName);
        $lastName = trim((string) $this->lastName);

        if ('' !== $firstName || '' !== $lastName) {
            return trim($firstName . ' ' . $lastName);
        }

        return (string) $this->email;
    }

    public function getConfirmationCode(): ?string
    {
        return $this->confirmationCode;
    }

    public function setConfirmationCode(?string $confirmationCode): self
    {
        $this->confirmationCode = $confirmationCode;

        return $this;
    }

    public function isConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    public function setIsConfirmed(bool $isConfirmed): self
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): self
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTime
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTime $resetTokenExpiresAt): self
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;

        return $this;
    }

    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function getPhone(): ?string
    {
        if (null === $this->phone || 12 !== strlen($this->phone)) {
            return $this->phone;
        }

        return sprintf(
            '+%s %s %s-%s-%s',
            substr($this->phone, 0, 3),  // +375
            substr($this->phone, 3, 2),  // код
            substr($this->phone, 5, 3),  // XXX
            substr($this->phone, 8, 2),  // XX
            substr($this->phone, 10, 2)  // XX
        );
    }

    public function setPhone(?string $phone): self
    {
        if (null !== $phone) {
            $digits = preg_replace('/\D+/', '', $phone);

            // Убрать лишние префиксы, если вводят, например, 8033...
            if (str_starts_with($digits, '80')) {
                $digits = '375' . substr($digits, 2);
            }

            if (!str_starts_with($digits, '375')) {
                $digits = '375' . $digits;
            }

            // Сохраняем ровно 12 цифр
            $this->phone = substr($digits, 0, 12);
        } else {
            $this->phone = null;
        }

        return $this;
    }
}
