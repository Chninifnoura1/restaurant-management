<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Form\PurchaseType;
use App\Repository\PurchaseRepository;
use App\Service\StockService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PurchaseController extends AbstractController
{
    #[Route('/purchase', name: 'app_purchase_index', methods: ['GET'])]
    public function index(PurchaseRepository $purchaseRepository): Response
    {
        return $this->render('purchase/index.html.twig', [
            'purchases' => $purchaseRepository->findBy(
                [],
                ['purchaseDate' => 'DESC']
            ),
        ]);
    }

    #[Route('/purchase/new', name: 'app_purchase_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        StockService $stockService
    ): Response {
        $purchase = new Purchase();

        $form = $this->createForm(PurchaseType::class, $purchase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $stockService->registerPurchase($purchase);

                $this->addFlash('success', 'Achat enregistré et stock mis à jour.');

                return $this->redirectToRoute('app_purchase_index');
            } catch (RuntimeException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        return $this->render('purchase/new.html.twig', [
            'form' => $form,
        ]);
    }
}
