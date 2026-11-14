<?php

namespace App\Service;

use App\Entity\Ingredient;
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
            throw new RuntimeException('Cette commande est annulée.');
        }

        if ($order->getStockDeductedAt() !== null) {
            throw new RuntimeException('Le stock a déjà été déduit pour cette commande.');
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
                throw new RuntimeException('La commande doit être enregistrée avant son service.');
            }

            // Verrouiller la commande pour éviter deux déductions simultanées.
            $this->entityManager->refresh(
                $order,
                LockMode::PESSIMISTIC_WRITE
            );

            if ($order->getStatus() === 'cancelled') {
                throw new RuntimeException('Impossible de servir une commande annulée.');
            }

            if ($order->getStockDeductedAt() !== null) {
                throw new RuntimeException('Le stock a déjà été déduit pour cette commande.');
            }

            if (!in_array($order->getStatus(), ['pending', 'in_preparation'], true)) {
                throw new RuntimeException('Le statut actuel ne permet pas de servir cette commande.');
            }

            $requirements = $this->calculateRequirements($order);

            // Verrouiller les ingrédients dans l'ordre des identifiants.
            ksort($requirements);

            foreach ($requirements as $requirement) {
                $this->entityManager->lock(
                    $requirement['ingredient'],
                    LockMode::PESSIMISTIC_WRITE
                );
            }

            // Vérifier à nouveau le stock après les verrouillages.
            $this->assertOrderHasEnoughStock($order);

            $now = new \DateTimeImmutable();

            foreach ($requirements as $requirement) {
                /** @var Ingredient $ingredient */
                $ingredient = $requirement['ingredient'];

                $currentStock = $this->toMilliUnits(
                    $ingredient->getStockQuantity()
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
     * Calcule les quantités nécessaires pour tous les plats de la commande.
     */
    private function calculateRequirements(RestaurantOrder $order): array
    {
        $requirements = [];

        if ($order->getItems()->isEmpty()) {
            throw new RuntimeException('La commande ne contient aucun plat.');
        }

        foreach ($order->getItems() as $orderItem) {
            $dish = $orderItem->getDish();
            $orderQuantity = $orderItem->getQuantity();

            if ($dish === null || $orderQuantity <= 0) {
                throw new RuntimeException('Un article de la commande est invalide.');
            }

            if ($dish->getIngredients()->isEmpty()) {
                throw new RuntimeException(
                    'Aucune recette définie pour le plat : ' . $dish->getName()
                );
            }

            foreach ($dish->getIngredients() as $dishIngredient) {
                $ingredient = $dishIngredient->getIngredient();

                if ($ingredient === null || $ingredient->getId() === null) {
                    throw new RuntimeException('Un ingrédient de la recette est invalide.');
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

        if (!preg_match('/^(-?)(\d+)(?:\.(\d{1,3}))?$/', $value, $matches)) {
            throw new RuntimeException('Valeur décimale invalide : ' . $value);
        }

        $fraction = str_pad($matches[3] ?? '', 3, '0');

        $result = ((int) $matches[2] * 1000) + (int) $fraction;

        return ($matches[1] ?? '') === '-' ? -$result : $result;
    }

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
