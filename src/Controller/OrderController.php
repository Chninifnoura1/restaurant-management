<?php

namespace App\Controller;

use App\Entity\OrderItem;
use App\Entity\RestaurantOrder;
use App\Form\OrderType;
use App\Repository\RestaurantOrderRepository;
use App\Service\StockService;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order', methods: ['GET'])]
    public function index(
        RestaurantOrderRepository $orderRepository
    ): Response {
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findBy(
                [],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    #[Route('/order/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        StockService $stockService
    ): Response {
        $order = new RestaurantOrder();
        $order->addItem(new OrderItem());

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($order->getItems()->isEmpty()) {
                    throw new RuntimeException(
                        'Ajoutez au moins un plat à la commande.'
                    );
                }

                foreach ($order->getItems() as $item) {
                    $dish = $item->getDish();

                    if ($dish === null) {
                        throw new RuntimeException(
                            'Veuillez choisir un plat pour chaque ligne.'
                        );
                    }

                    if (!$dish->isAvailable()) {
                        throw new RuntimeException(
                            'Le plat ' . $dish->getName() . ' est indisponible.'
                        );
                    }

                    if (
                        $item->getQuantity() === null
                        || $item->getQuantity() <= 0
                    ) {
                        throw new RuntimeException(
                            'La quantité doit être supérieure à zéro.'
                        );
                    }

                    // Conserver le prix au moment de la commande.
                    $item->setUnitPrice($dish->getSalePrice());
                    $item->setRestaurantOrder($order);
                }

                // Vérifier le stock sans le déduire.
                $stockService->assertOrderHasEnoughStock($order);

                $em->persist($order);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Commande créée avec succès.'
                );

                return $this->redirectToRoute('app_order');

            } catch (RuntimeException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        return $this->render('order/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/order/kitchen', name: 'app_order_kitchen', methods: ['GET'])]
    public function kitchen(
        RestaurantOrderRepository $orderRepository
    ): Response {
        return $this->render('order/kitchen.html.twig', [
            'orders' => $orderRepository->findBy(
                [
                    'status' => ['pending', 'in_preparation'],
                ],
                [
                    'createdAt' => 'ASC',
                ]
            ),
        ]);
    }

    #[Route(
        '/order/{id}/prepare',
        name: 'app_order_prepare',
        methods: ['POST']
    )]
    public function prepare(
        int $id,
        Request $request,
        RestaurantOrderRepository $orderRepository,
        EntityManagerInterface $em
    ): Response {
        $order = $orderRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException(
                'Commande introuvable.'
            );
        }

        if (!$this->isCsrfTokenValid(
            'prepare-order-' . $order->getId(),
            $request->request->get('_token')
        )) {
            $this->addFlash('danger', 'Requête invalide.');

            return $this->redirectToRoute('app_order_kitchen');
        }

        if ($order->getStatus() !== 'pending') {
            $this->addFlash(
                'danger',
                'Cette commande ne peut pas être mise en préparation.'
            );

            return $this->redirectToRoute('app_order_kitchen');
        }

        $order->setStatus('in_preparation');

        // Aucun stock n'est déduit pendant la préparation.
        $em->flush();

        $this->addFlash(
            'success',
            'Commande passée en préparation.'
        );

        return $this->redirectToRoute('app_order_kitchen');
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
