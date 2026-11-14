<?php

namespace App\Entity;

use App\Repository\RestaurantOrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantOrderRepository::class)]
class RestaurantOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $servedAt = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $tableNumber = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'restaurantOrder')]
    private Collection $items;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $stockDeductedAt = null;

    public function __construct()
    {
    $this->createdAt = new \DateTimeImmutable();
    $this->status = 'pending';
    $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    public function getServedAt(): ?\DateTimeImmutable
    {
        return $this->servedAt;
    }

    public function setServedAt(?\DateTimeImmutable $servedAt): static
    {
        $this->servedAt = $servedAt;

        return $this;
    }

    public function getTableNumber(): ?string
    {
        return $this->tableNumber;
    }

    public function setTableNumber(?string $tableNumber): static
    {
        $this->tableNumber = $tableNumber;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setRestaurantOrder($this);
        }

        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getRestaurantOrder() === $this) {
                $item->setRestaurantOrder(null);
            }
        }

        return $this;
    }

    public function getStockDeductedAt(): ?\DateTimeImmutable
    {
        return $this->stockDeductedAt;
    }

    public function setStockDeductedAt(?\DateTimeImmutable $stockDeductedAt): static
    {
        $this->stockDeductedAt = $stockDeductedAt;

        return $this;
    }
}
