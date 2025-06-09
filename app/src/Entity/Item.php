<?php

namespace App\Entity;

use App\Enum\ItemPublishStateEnum;
use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\Table(
    name: 'item',
    indexes: [
        new ORM\Index(name: 'idx_item_publish_state', columns: ['publish_state']),
    ]
)]
class Item extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ORM\Column(type: Types::GUID, nullable: false)]
    private string $guid;

    #[ORM\Column(type: 'string', length: 50, enumType: ItemPublishStateEnum::class)]
    private ItemPublishStateEnum $publishState;

    #[ORM\Column(type: 'string', length: 50, unique: true, options: ['comment' => 'Артикул товара'])]
    private string $sku;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $url;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\ManyToMany(targetEntity: Category::class)]
    #[ORM\JoinTable(
        name: 'item_category',
        joinColumns: [new ORM\JoinColumn(name: 'item_id', referencedColumnName: 'guid')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'category_id', referencedColumnName: 'guid')]
    )]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): static
    {
        $this->guid = $guid;

        return $this;
    }

    public function getPublishState(): ItemPublishStateEnum
    {
        return $this->publishState;
    }

    public function setPublishState(ItemPublishStateEnum $publishState): static
    {
        $this->publishState = $publishState;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): static
    {
        $this->sku = $sku;

        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }
}
