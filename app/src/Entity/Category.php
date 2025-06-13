<?php

namespace App\Entity;

use App\Enum\CategoryPublishStateEnum;
use App\EventListener\Doctrine\UrlAndBreadcrumbsListener;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\EntityListeners([UrlAndBreadcrumbsListener::class])]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(
    name: 'category',
    indexes: [
        new ORM\Index(name: 'idx_category_publish_state', columns: ['publish_state']),
        new ORM\Index(name: 'idx_category_parent_id', columns: ['parent_id']),
        new ORM\Index(name: 'idx_category_name', columns: ['name']),
    ]
)]
class Category extends BaseEntity
{
    public const int LISTING_LIMIT = 20;

    public const string URL_CATALOG = '/catalog';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ORM\Column(type: Types::GUID, nullable: false)]
    private string $guid;

    #[ORM\Column(type: 'string', length: 50, enumType: CategoryPublishStateEnum::class)]
    private CategoryPublishStateEnum $publishState;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $url;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array $breadcrumbs = [];

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'guid', nullable: true, onDelete: 'SET NULL')]
    private ?Category $parent = null;

    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'parent')]
    private Collection $children;

    #[ORM\ManyToMany(targetEntity: Item::class, mappedBy: 'categories')]
    private Collection $items;

    public function __construct()
    {
        parent::__construct();

        $this->items = new ArrayCollection();
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

    public function getPublishState(): CategoryPublishStateEnum
    {
        return $this->publishState;
    }

    public function setPublishState(CategoryPublishStateEnum $publishState): static
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

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setParent(?Category $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): static
    {
        $this->children = $children;

        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
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
}
