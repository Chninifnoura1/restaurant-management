<?php

namespace App\Service;

use App\Entity\Ingredient;
use App\Entity\Loss;
use App\Entity\Purchase;
use App\Entity\RestaurantOrder;
use App\Entity\StockMovement;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Throwable;

class StockService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Vérifie si le stock est suffisant pour une commande.
     * Cette méthode ne réserve et ne déduit aucun stock.
     */
    public function assertOrderHasEnoughStock(RestaurantOrder $order): void
    {
        if ($order->getStatus() === 'cancelled') {
            throw new RuntimeException(
                'Cette commande est annulée.'
            );
        }

        if ($order->getStockDeductedAt() !== null) {
            throw new RuntimeException(
                'Le stock a déjà été déduit pour cette commande.'
            );
        }

        $requirements = $this->calculateRequirements($order);

        foreach ($requirements as $requirement) {
            /** @var Ingredient $ingredient */
            $ingredient = $requirement['ingredient'];

            $available = $this->toMilliUnits(
                $ingredient->getStockQuantity() ?? '0'
            );

            if ($available < $requirement['quantity']) {
                throw new RuntimeException(sprintf(
                    'Stock insuffisant pour %s. Disponible : %s %s. Nécessaire : %s %s.',
                    $ingredient->getName(),
                    $ingredient->getStockQuantity(),
                    $ingredient->getUnit(),
                    $this->formatMilliUnits($requirement['quantity']),
                    $ingredient->getUnit()
                ));
            }
        }
    }

    /**
     * Déduit le stock une seule fois lorsque la commande est servie.
     */
    public function serveOrder(RestaurantOrder $order): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            if ($order->getId() === null) {
                throw new RuntimeException(
                    'La commande doit être enregistrée avant son service.'
                );
            }

            $this->entityManager->refresh(
                $order,
                LockMode::PESSIMISTIC_WRITE
            );

            if ($order->getStatus() === 'cancelled') {
                throw new RuntimeException(
                    'Impossible de servir une commande annulée.'
                );
            }

            if ($order->getStockDeductedAt() !== null) {
                throw new RuntimeException(
                    'Le stock a déjà été déduit pour cette commande.'
                );
            }

            if (!in_array(
                $order->getStatus(),
                ['pending', 'in_preparation'],
                true
            )) {
                throw new RuntimeException(
                    'Le statut actuel ne permet pas de servir cette commande.'
                );
            }

            $requirements = $this->calculateRequirements($order);

            ksort($requirements);

            foreach ($requirements as $requirement) {
                $this->entityManager->lock(
                    $requirement['ingredient'],
                    LockMode::PESSIMISTIC_WRITE
                );
            }

            $this->assertOrderHasEnoughStock($order);

            $now = new \DateTimeImmutable();

            foreach ($requirements as $requirement) {
                /** @var Ingredient $ingredient */
                $ingredient = $requirement['ingredient'];

                $currentStock = $this->toMilliUnits(
                    $ingredient->getStockQuantity() ?? '0'
                );

                $newStock = $currentStock - $requirement['quantity'];

                if ($newStock < 0) {
                    throw new RuntimeException(
                        'Le stock ne peut pas devenir négatif.'
                    );
                }

                $ingredient->setStockQuantity(
                    $this->formatMilliUnits($newStock)
                );

                $movement = new StockMovement();
                $movement->setType('OUT_ORDER');
                $movement->setQuantity(
                    $this->formatMilliUnits($requirement['quantity'])
                );
                $movement->setReason(
                    'Consommation de la commande #' . $order->getId()
                );
                $movement->setCreatedAt($now);
                $movement->setIngredient($ingredient);

                $this->entityManager->persist($movement);
            }

            $order->setStatus('served');
            $order->setServedAt($now);
            $order->setStockDeductedAt($now);

            $this->entityManager->flush();
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Enregistre un achat, augmente le stock
     * et crée les mouvements entrants.
     */
    public function registerPurchase(Purchase $purchase): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $items = $purchase->getPurchaseItems();

            if ($items->isEmpty()) {
                throw new RuntimeException(
                    'Un achat doit contenir au moins un ingrédient.'
                );
            }

            $ingredients = [];

            foreach ($items as $item) {
                if (
                    $item->getQuantity() === null ||
                    $item->getUnitPrice() === null
                ) {
                    throw new RuntimeException(
                        'La quantité et le prix sont obligatoires.'
                    );
                }

                $ingredient = $item->getIngredient();

                if (
                    $ingredient === null ||
                    $ingredient->getId() === null
                ) {
                    throw new RuntimeException(
                        'Un ingrédient est invalide.'
                    );
                }

                $quantity = $this->toMilliUnits($item->getQuantity());
                $unitPrice = $this->toCents($item->getUnitPrice());

                if ($quantity <= 0) {
                    throw new RuntimeException(
                        'La quantité achetée doit être positive.'
                    );
                }

                if ($unitPrice < 0) {
                    throw new RuntimeException(
                        'Le prix unitaire ne peut pas être négatif.'
                    );
                }

                $ingredients[$ingredient->getId()] = $ingredient;
            }

            ksort($ingredients);

            foreach ($ingredients as $ingredient) {
                $this->entityManager->lock(
                    $ingredient,
                    LockMode::PESSIMISTIC_WRITE
                );
            }

            $this->entityManager->persist($purchase);
            $this->entityManager->flush();

            $totalCents = 0;
            $now = new \DateTimeImmutable();

            foreach ($items as $item) {
                $ingredient = $item->getIngredient();

                $quantity = $this->toMilliUnits($item->getQuantity());
                $unitPrice = $this->toCents($item->getUnitPrice());

                $item->setPurchase($purchase);
                $this->entityManager->persist($item);

                $currentStock = $this->toMilliUnits(
                    $ingredient->getStockQuantity() ?? '0'
                );

                $ingredient->setStockQuantity(
                    $this->formatMilliUnits($currentStock + $quantity)
                );

                $lineTotal = intdiv(
                    ($quantity * $unitPrice) + 500,
                    1000
                );

                $totalCents += $lineTotal;

                $movement = new StockMovement();
                $movement->setType('IN_PURCHASE');
                $movement->setQuantity(
                    $this->formatMilliUnits($quantity)
                );
                $movement->setReason(
                    'Achat #' . $purchase->getId()
                );
                $movement->setCreatedAt($now);
                $movement->setIngredient($ingredient);

                $this->entityManager->persist($movement);
            }

            $purchase->setTotalAmount(
                $this->formatCents($totalCents)
            );

            $this->entityManager->flush();
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Enregistre une perte, diminue le stock
     * et crée un mouvement de sortie OUT_LOSS.
     */
    public function registerLoss(Loss $loss): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $ingredient = $loss->getIngredient();

            if (
                $ingredient === null ||
                $ingredient->getId() === null
            ) {
                throw new RuntimeException(
                    'Un ingrédient valide doit être sélectionné.'
                );
            }

            if ($loss->getQuantity() === null) {
                throw new RuntimeException(
                    'La quantité perdue est obligatoire.'
                );
            }

            $quantity = $this->toMilliUnits($loss->getQuantity());

            if ($quantity <= 0) {
                throw new RuntimeException(
                    'La quantité perdue doit être positive.'
                );
            }

            $reason = trim($loss->getReason() ?? '');

            if ($reason === '') {
                throw new RuntimeException(
                    'Le motif de la perte est obligatoire.'
                );
            }

            $this->entityManager->lock(
                $ingredient,
                LockMode::PESSIMISTIC_WRITE
            );

            $currentStock = $this->toMilliUnits(
                $ingredient->getStockQuantity() ?? '0'
            );

            if ($currentStock < $quantity) {
                throw new RuntimeException(sprintf(
                    'Stock insuffisant pour %s. Disponible : %s %s.',
                    $ingredient->getName(),
                    $ingredient->getStockQuantity(),
                    $ingredient->getUnit()
                ));
            }

            $newStock = $currentStock - $quantity;

            if ($newStock < 0) {
                throw new RuntimeException(
                    'Le stock ne peut pas devenir négatif.'
                );
            }

            $ingredient->setStockQuantity(
                $this->formatMilliUnits($newStock)
            );

            $now = new \DateTimeImmutable();

            $loss->setReason($reason);
            $loss->setCreatedAt($now);

            $this->entityManager->persist($loss);

            $movement = new StockMovement();
            $movement->setType('OUT_LOSS');
            $movement->setQuantity(
                $this->formatMilliUnits($quantity)
            );
            $movement->setReason($reason);
            $movement->setCreatedAt($now);
            $movement->setIngredient($ingredient);

            $this->entityManager->persist($movement);

            $this->entityManager->flush();
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Effectue un inventaire physique et régularise le stock.
     *
     * Le mouvement enregistre l'écart signé :
     * positif = entrée, négatif = sortie.
     */
    public function adjustInventory(
        Ingredient $ingredient,
        string $physicalQuantity,
        string $reason
    ): void {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            if ($ingredient->getId() === null) {
                throw new RuntimeException(
                    'L ingrédient doit être enregistré.'
                );
            }

            $physical = $this->toMilliUnits($physicalQuantity);

            if ($physical < 0) {
                throw new RuntimeException(
                    'Le stock physique ne peut pas être négatif.'
                );
            }

            $reason = trim($reason);

            if ($reason === '') {
                throw new RuntimeException(
                    'Le motif de l inventaire est obligatoire.'
                );
            }

            $this->entityManager->refresh(
                $ingredient,
                LockMode::PESSIMISTIC_WRITE
            );

            $theoretical = $this->toMilliUnits(
                $ingredient->getStockQuantity() ?? '0'
            );

            $difference = $physical - $theoretical;

            $ingredient->setStockQuantity(
                $this->formatMilliUnits($physical)
            );

            $now = new \DateTimeImmutable();

            $movement = new StockMovement();
            $movement->setType('INVENTORY_ADJUSTMENT');
            $movement->setQuantity(
                $this->formatMilliUnits($difference)
            );
            $movement->setReason(sprintf(
                '%s | Théorique: %s | Physique: %s',
                $reason,
                $this->formatMilliUnits($theoretical),
                $this->formatMilliUnits($physical)
            ));
            $movement->setCreatedAt($now);
            $movement->setIngredient($ingredient);

            $this->entityManager->persist($movement);
            $this->entityManager->flush();

            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Calcule les quantités nécessaires
     * pour tous les plats de la commande.
     */
    private function calculateRequirements(RestaurantOrder $order): array
    {
        $requirements = [];

        if ($order->getItems()->isEmpty()) {
            throw new RuntimeException(
                'La commande ne contient aucun plat.'
            );
        }

        foreach ($order->getItems() as $orderItem) {
            $dish = $orderItem->getDish();
            $orderQuantity = $orderItem->getQuantity();

            if ($dish === null || $orderQuantity <= 0) {
                throw new RuntimeException(
                    'Un article de la commande est invalide.'
                );
            }

            if ($dish->getIngredients()->isEmpty()) {
                throw new RuntimeException(
                    'Aucune recette définie pour le plat : ' . $dish->getName()
                );
            }

            foreach ($dish->getIngredients() as $dishIngredient) {
                $ingredient = $dishIngredient->getIngredient();

                if (
                    $ingredient === null ||
                    $ingredient->getId() === null
                ) {
                    throw new RuntimeException(
                        'Un ingrédient de la recette est invalide.'
                    );
                }

                $recipeQuantity = $this->toMilliUnits(
                    $dishIngredient->getQuantity()
                );

                if ($recipeQuantity <= 0) {
                    throw new RuntimeException(
                        'La quantité de recette doit être positive.'
                    );
                }

                $ingredientId = $ingredient->getId();

                if (!isset($requirements[$ingredientId])) {
                    $requirements[$ingredientId] = [
                        'ingredient' => $ingredient,
                        'quantity' => 0,
                    ];
                }

                $requirements[$ingredientId]['quantity']
                    += $recipeQuantity * $orderQuantity;
            }
        }

        return $requirements;
    }

    /**
     * Convertit un nombre décimal avec 3 chiffres après la virgule
     * en entier pour éviter les erreurs de calcul avec les floats.
     */
    private function toMilliUnits(string $value): int
    {
        $value = trim($value);

        if (!preg_match(
            '/^(-?)(\d+)(?:\.(\d{1,3}))?$/',
            $value,
            $matches
        )) {
            throw new RuntimeException(
                'Valeur décimale invalide : ' . $value
            );
        }

        $fraction = str_pad($matches[3] ?? '', 3, '0');

        $result = ((int) $matches[2] * 1000) + (int) $fraction;

        return ($matches[1] ?? '') === '-' ? -$result : $result;
    }

    /**
     * Convertit un prix décimal en centimes.
     */
    private function toCents(string $value): int
    {
        $value = trim($value);

        if (!preg_match(
            '/^(\d+)(?:\.(\d{1,2}))?$/',
            $value,
            $matches
        )) {
            throw new RuntimeException(
                'Prix invalide : ' . $value
            );
        }

        $fraction = str_pad($matches[2] ?? '', 2, '0');

        return ((int) $matches[1] * 100) + (int) $fraction;
    }

    /**
     * Convertit les centimes en montant décimal.
     */
    private function formatCents(int $value): string
    {
        return sprintf(
            '%d.%02d',
            intdiv($value, 100),
            $value % 100
        );
    }

    /**
     * Convertit les milli-unités en nombre décimal.
     */
    private function formatMilliUnits(int $value): string
    {
        $sign = $value < 0 ? '-' : '';
        $value = abs($value);

        return sprintf(
            '%s%d.%03d',
            $sign,
            intdiv($value, 1000),
            $value % 1000
        );
    }
}
