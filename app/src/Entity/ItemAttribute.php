<?php

namespace App\Entity;

use App\Repository\ItemAttributeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemAttributeRepository::class)]
class ItemAttribute extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ORM\Column(type: Types::GUID, nullable: false)]
    private string $guid;

    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'itemAttributes')]
    #[ORM\JoinColumn(name: 'item_guid', referencedColumnName: 'guid', nullable: false)]
    private Item $item;

    #[ORM\ManyToOne(targetEntity: Attribute::class)]
    #[ORM\JoinColumn(name: 'attribute_guid', referencedColumnName: 'guid', nullable: false)]
    private Attribute $attribute;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $value = null;

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): static
    {
        $this->guid = $guid;

        return $this;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): static
    {
        $this->item = $item;

        return $this;
    }

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(Attribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
