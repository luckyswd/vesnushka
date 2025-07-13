<?php

namespace App\Entity;

use App\Enum\CurrencyEnum;
use App\Enum\PaymentStatusEnum;
use App\Enum\PriceTypeEnum;
use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ORM\Column(type: Types::GUID, nullable: false)]
    #[Groups(['json_cart'])]
    private string $guid;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'carts')]
    #[ORM\JoinColumn(name: 'user_guid', referencedColumnName: 'guid', nullable: true)]
    #[Groups(['json_cart'])]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 50, enumType: CurrencyEnum::class)]
    #[Groups(['json_cart'])]
    private CurrencyEnum $currency = CurrencyEnum::BYN;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: PriceTypeEnum::class)]
    #[Groups(['json_cart'])]
    private PriceTypeEnum $priceType = PriceTypeEnum::RETAIL;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => 0])]
    #[Groups(['json_cart'])]
    private string $totalAmount = '0';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $active = true;

    #[ORM\Column(length: 36, nullable: true)]
    private ?string $sessionToken = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Groups(['json_cart'])]
    private ?string $deliveryMethod = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => 0])]
    #[Groups(['json_cart'])]
    private string $deliveryCost = '0';

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, enumType: PaymentStatusEnum::class)]
    #[Groups(['json_cart'])]
    private ?PaymentStatusEnum $paymentStatus = null;

    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'cart')]
    #[Groups(['json_cart'])]
    private Collection $cartItems;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups(['json_cart'])]
    private ?string $deliveryCity = null;

    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCurrency(): CurrencyEnum
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyEnum $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getSessionToken(): ?string
    {
        return $this->sessionToken;
    }

    public function setSessionToken(?string $sessionToken): self
    {
        $this->sessionToken = $sessionToken;

        return $this;
    }

    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function setCartItems(Collection $cartItems): void
    {
        $this->cartItems = $cartItems;
    }

    public function getDeliveryMethod(): ?string
    {
        return $this->deliveryMethod;
    }

    public function setDeliveryMethod(?string $deliveryMethod): self
    {
        $this->deliveryMethod = $deliveryMethod;

        return $this;
    }

    public function getDeliveryCost(): int
    {
        return $this->deliveryCost;
    }

    public function setDeliveryCost(int $deliveryCost): self
    {
        $this->deliveryCost = $deliveryCost;

        return $this;
    }

    public function getPaymentStatus(): ?PaymentStatusEnum
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?PaymentStatusEnum $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getPriceType(): PriceTypeEnum
    {
        return $this->priceType;
    }

    public function setPriceType(PriceTypeEnum $priceType): self
    {
        $this->priceType = $priceType;
        return $this;
    }

    #[Groups(['json_cart'])]
    public function getCountCartItems(): int
    {
        $total = 0;

        foreach ($this->cartItems as $cartItem) {
            $total += $cartItem->getQty();
        }

        return $total;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems[] = $cartItem;
            $cartItem->setCart($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->contains($cartItem)) {
            $this->cartItems->removeElement($cartItem);
        }

        return $this;
    }

    public function getDeliveryCity(): ?string
    {
        return $this->deliveryCity;
    }

    public function setDeliveryCity(?string $deliveryCity): self
    {
        $this->deliveryCity = $deliveryCity;

        return $this;
    }
}
