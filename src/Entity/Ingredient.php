<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private ?string $unit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3)]
    private ?string $stockQuantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3)]
    private ?string $alertThreshold = null;

    /**
     * @var Collection<int, DishIngredient>
     */
    #[ORM\OneToMany(targetEntity: DishIngredient::class, mappedBy: 'ingredient')]
    private Collection $dishIngredients;

    /**
     * @var Collection<int, PurchaseItem>
     */
    #[ORM\OneToMany(targetEntity: PurchaseItem::class, mappedBy: 'ingredient')]
    private Collection $purchaseItems;

    /**
     * @var Collection<int, StockMovement>
     */
    #[ORM\OneToMany(targetEntity: StockMovement::class, mappedBy: 'ingredient')]
    private Collection $stockMovements;

    /**
     * @var Collection<int, Loss>
     */
    #[ORM\OneToMany(targetEntity: Loss::class, mappedBy: 'ingredient')]
    private Collection $losses;

    public function __construct()
    {
        $this->dishIngredients = new ArrayCollection();
        $this->purchaseItems = new ArrayCollection();
        $this->stockMovements = new ArrayCollection();
        $this->losses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function getStockQuantity(): ?string
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(string $stockQuantity): static
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    public function getAlertThreshold(): ?string
    {
        return $this->alertThreshold;
    }

    public function setAlertThreshold(string $alertThreshold): static
    {
        $this->alertThreshold = $alertThreshold;

        return $this;
    }

    /**
     * @return Collection<int, DishIngredient>
     */
    public function getDishIngredients(): Collection
    {
        return $this->dishIngredients;
    }

    public function addDishIngredient(DishIngredient $dishIngredient): static
    {
        if (!$this->dishIngredients->contains($dishIngredient)) {
            $this->dishIngredients->add($dishIngredient);
            $dishIngredient->setIngredient($this);
        }

        return $this;
    }

    public function removeDishIngredient(DishIngredient $dishIngredient): static
    {
        if ($this->dishIngredients->removeElement($dishIngredient)) {
            // set the owning side to null (unless already changed)
            if ($dishIngredient->getIngredient() === $this) {
                $dishIngredient->setIngredient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PurchaseItem>
     */
    public function getPurchaseItems(): Collection
    {
        return $this->purchaseItems;
    }

    public function addPurchaseItem(PurchaseItem $purchaseItem): static
    {
        if (!$this->purchaseItems->contains($purchaseItem)) {
            $this->purchaseItems->add($purchaseItem);
            $purchaseItem->setIngredient($this);
        }

        return $this;
    }

    public function removePurchaseItem(PurchaseItem $purchaseItem): static
    {
        if ($this->purchaseItems->removeElement($purchaseItem)) {
            // set the owning side to null (unless already changed)
            if ($purchaseItem->getIngredient() === $this) {
                $purchaseItem->setIngredient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StockMovement>
     */
    public function getStockMovements(): Collection
    {
        return $this->stockMovements;
    }

    public function addStockMovement(StockMovement $stockMovement): static
    {
        if (!$this->stockMovements->contains($stockMovement)) {
            $this->stockMovements->add($stockMovement);
            $stockMovement->setIngredient($this);
        }

        return $this;
    }

    public function removeStockMovement(StockMovement $stockMovement): static
    {
        if ($this->stockMovements->removeElement($stockMovement)) {
            // set the owning side to null (unless already changed)
            if ($stockMovement->getIngredient() === $this) {
                $stockMovement->setIngredient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Loss>
     */
    public function getLosses(): Collection
    {
        return $this->losses;
    }

    public function addLoss(Loss $loss): static
    {
        if (!$this->losses->contains($loss)) {
            $this->losses->add($loss);
            $loss->setIngredient($this);
        }

        return $this;
    }

    public function removeLoss(Loss $loss): static
    {
        if ($this->losses->removeElement($loss)) {
            // set the owning side to null (unless already changed)
            if ($loss->getIngredient() === $this) {
                $loss->setIngredient(null);
            }
        }

        return $this;
    }
}
