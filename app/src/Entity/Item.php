<?php

namespace App\Entity;

use App\Enum\ItemPublishStateEnum;
use App\EventListener\Doctrine\UrlAndBreadcrumbsListener;
use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\EntityListeners([UrlAndBreadcrumbsListener::class])]
#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\Table(
    name: 'item',
    indexes: [
        new ORM\Index(name: 'idx_item_publish_state', columns: ['publish_state']),
        new ORM\Index(name: 'idx_item_sku', columns: ['sku']),
        new ORM\Index(name: 'idx_item_url', columns: ['url']),
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

    #[ORM\Column(type: Types::STRING, length: 50, unique: true, options: ['comment' => 'Артикул товара'])]
    private string $sku;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $url;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array $breadcrumbs = [];

    #[ORM\Column(type: Types::JSON, nullable: false, options: ['comment' => 'Атрибуты товара (хранятся в jsonb)'])]
    private array $attributes = [];

    #[ORM\Column(type: Types::JSON, nullable: false, options: ['comment' => 'Цены товара (хранятся в jsonb)'])]
    private array $price = [];

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'items')]
    #[ORM\JoinTable(
        name: 'item_category',
        joinColumns: [new ORM\JoinColumn(name: 'item_guid', referencedColumnName: 'guid')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'category_guid', referencedColumnName: 'guid')]
    )]
    private Collection $categories;

    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'brand_guid', referencedColumnName: 'guid', nullable: false)]
    private Brand $brand;

    #[ORM\Column(type: Types::INTEGER)]
    private int $stock = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $rank = 0;

    public function __construct()
    {
        parent::__construct();

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

    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    public function setBreadcrumbs(array $breadcrumbs): static
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getBrand(): Brand
    {
        return $this->brand;
    }

    public function setBrand(Brand $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getPrice(): array
    {
        return $this->price;
    }

    public function setPrice(array $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }
}
