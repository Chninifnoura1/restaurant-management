<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\StockMovement;
use App\Form\InventoryCountType;
use App\Repository\IngredientRepository;
use App\Service\StockService;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventory')]
final class InventoryController extends AbstractController
{
    #[Route('/', name: 'app_inventory_index', methods: ['GET'])]
    public function index(
        IngredientRepository $ingredientRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $movements = $entityManager
            ->getRepository(StockMovement::class)
            ->findBy(
                ['type' => 'INVENTORY_ADJUSTMENT'],
                ['createdAt' => 'DESC'],
                50
            );

        return $this->render('inventory/index.html.twig', [
            'ingredients' => $ingredientRepository->findBy(
                [],
                ['name' => 'ASC']
            ),
            'movements' => $movements,
        ]);
    }

    #[Route('/count', name: 'app_inventory_count', methods: ['GET', 'POST'])]
    public function count(
        Request $request,
        StockService $stockService
    ): Response {
        $form = $this->createForm(InventoryCountType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                /** @var Ingredient $ingredient */
                $ingredient = $data['ingredient'];

                $stockService->adjustInventory(
                    $ingredient,
                    (string) $data['physicalQuantity'],
                    (string) $data['reason']
                );

                $this->addFlash(
                    'success',
                    'Inventaire enregistre. Le stock a ete ajuste.'
                );

                return $this->redirectToRoute('app_inventory_index');
            } catch (RuntimeException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        return $this->render('inventory/count.html.twig', [
            'form' => $form,
        ]);
    }
}