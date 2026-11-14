<?php

namespace App\Controller;

use App\Repository\RestaurantOrderRepository;
use App\Service\StockService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order', methods: ['GET'])]
    public function index(
        RestaurantOrderRepository $orderRepository
    ): Response {
        $orders = $orderRepository->findBy(
            [],
            ['createdAt' => 'DESC']
        );

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route(
        '/order/{id}/serve',
        name: 'app_order_serve',
        methods: ['POST']
    )]
    public function serve(
        int $id,
        Request $request,
        RestaurantOrderRepository $orderRepository,
        StockService $stockService
    ): Response {
        $order = $orderRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException(
                'Commande introuvable.'
            );
        }

        if (!$this->isCsrfTokenValid(
            'serve-order-' . $order->getId(),
            $request->request->get('_token')
        )) {
            $this->addFlash('danger', 'Requête invalide.');
            return $this->redirectToRoute('app_order');
        }

        try {
            $stockService->serveOrder($order);

            $this->addFlash(
                'success',
                'Commande servie et stock mis à jour avec succès.'
            );
        } catch (RuntimeException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('app_order');
    }
}
