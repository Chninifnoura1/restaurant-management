<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Entity\Ingredient;
use App\Entity\Purchase;
use App\Entity\RestaurantOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $orders = $entityManager->getRepository(RestaurantOrder::class)->findAll();
        $dishes = $entityManager->getRepository(Dish::class)->findAll();
        $ingredients = $entityManager->getRepository(Ingredient::class)->findAll();
        $purchases = $entityManager->getRepository(Purchase::class)->findAll();

        // 1. Statistiques des commandes et chiffre d'affaires
        $totalOrders = count($orders);
        $servedOrders = 0;
        $revenue = 0.0;
        $bestSellers = [];

        foreach ($orders as $order) {
            if ($order->getStatus() !== 'served') {
                continue;
            }

            $servedOrders++;

            foreach ($order->getItems() as $item) {
                $quantity = $item->getQuantity();
                $unitPrice = (float) $item->getUnitPrice();

                $revenue += $quantity * $unitPrice;

                $dish = $item->getDish();

                if ($dish === null) {
                    continue;
                }

                $dishId = $dish->getId();

                if (!isset($bestSellers[$dishId])) {
                    $bestSellers[$dishId] = [
                        'name' => $dish->getName(),
                        'quantity' => 0,
                    ];
                }

                $bestSellers[$dishId]['quantity'] += $quantity;
            }
        }

        usort(
            $bestSellers,
            fn (array $a, array $b) => $b['quantity'] <=> $a['quantity']
        );

        // 2. Calcul du coût moyen pondéré des ingrédients
        $ingredientCosts = [];

        foreach ($purchases as $purchase) {
            foreach ($purchase->getPurchaseItems() as $item) {
                $ingredient = $item->getIngredient();

                if ($ingredient === null) {
                    continue;
                }

                $ingredientId = $ingredient->getId();
                $quantity = (float) $item->getQuantity();
                $unitPrice = (float) $item->getUnitPrice();

                if ($quantity <= 0) {
                    continue;
                }

                if (!isset($ingredientCosts[$ingredientId])) {
                    $ingredientCosts[$ingredientId] = [
                        'totalQuantity' => 0.0,
                        'totalCost' => 0.0,
                    ];
                }

                $ingredientCosts[$ingredientId]['totalQuantity'] += $quantity;
                $ingredientCosts[$ingredientId]['totalCost'] += $quantity * $unitPrice;
            }
        }

        foreach ($ingredientCosts as $ingredientId => $cost) {
            $ingredientCosts[$ingredientId]['averagePrice'] =
                $cost['totalQuantity'] > 0
                    ? $cost['totalCost'] / $cost['totalQuantity']
                    : 0.0;
        }

        // 3. Ingrédients dont le stock est faible
        $lowStockIngredients = [];

        foreach ($ingredients as $ingredient) {
            $stock = (float) ($ingredient->getStockQuantity() ?? 0);
            $threshold = (float) ($ingredient->getAlertThreshold() ?? 0);

            if ($stock <= $threshold) {
                $lowStockIngredients[] = [
                    'name' => $ingredient->getName(),
                    'stock' => $stock,
                    'threshold' => $threshold,
                    'unit' => $ingredient->getUnit(),
                ];
            }
        }

        // 4. Marge estimée par plat
        $dishMargins = [];

        foreach ($dishes as $dish) {
            $ingredientCost = 0.0;
            $missingCosts = [];

            foreach ($dish->getIngredients() as $dishIngredient) {
                $ingredient = $dishIngredient->getIngredient();

                if ($ingredient === null) {
                    $missingCosts[] = 'Ingrédient inconnu';
                    continue;
                }

                $ingredientId = $ingredient->getId();
                $recipeQuantity = (float) $dishIngredient->getQuantity();

                if (!isset($ingredientCosts[$ingredientId])) {
                    $missingCosts[] = $ingredient->getName();
                    continue;
                }

                $averagePrice = $ingredientCosts[$ingredientId]['averagePrice'];

                $ingredientCost += $recipeQuantity * $averagePrice;
            }

            $salePrice = (float) ($dish->getSalePrice() ?? 0);
            $hasCompleteCost = count($missingCosts) === 0
                && $dish->getIngredients()->count() > 0;

            $dishMargins[] = [
                'name' => $dish->getName(),
                'salePrice' => $salePrice,
                'ingredientCost' => $hasCompleteCost ? $ingredientCost : null,
                'margin' => $hasCompleteCost ? $salePrice - $ingredientCost : null,
                'missingCosts' => $missingCosts,
                'hasCompleteCost' => $hasCompleteCost,
            ];
        }

        usort(
            $dishMargins,
            function (array $a, array $b): int {
                if ($a['margin'] === null) {
                    return 1;
                }

                if ($b['margin'] === null) {
                    return -1;
                }

                return $b['margin'] <=> $a['margin'];
            }
        );

        return $this->render('dashboard/index.html.twig', [
            'totalOrders' => $totalOrders,
            'servedOrders' => $servedOrders,
            'revenue' => $revenue,
            'bestSellers' => array_slice($bestSellers, 0, 5),
            'lowStockIngredients' => $lowStockIngredients,
            'dishMargins' => $dishMargins,
        ]);
    }
}
