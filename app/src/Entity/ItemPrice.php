<?php

namespace App\Entity;

use App\Enum\PriceTypeEnum;
use App\Enum\CurrencyEnum;
use App\Repository\ItemPriceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemPriceRepository::class)]
class ItemPrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ORM\Column(type: Types::GUID, nullable: false)]
    private string $guid;

    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(name: 'item_guid', referencedColumnName: 'guid', nullable: false)]
    private Item $item;

    #[ORM\Column(enumType: PriceTypeEnum::class)]
    private PriceTypeEnum $priceType;

    #[ORM\Column(type: Types::STRING)]
    private string $price;

    #[ORM\Column(enumType: CurrencyEnum::class)]
    private CurrencyEnum $currency;

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        $this->item = $item;

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

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

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
}
