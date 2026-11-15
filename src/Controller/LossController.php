<?php

namespace App\Controller;

use App\Entity\Loss;
use App\Form\LossType;
use App\Repository\LossRepository;
use App\Service\StockService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LossController extends AbstractController
{
    #[Route('/loss', name: 'app_loss_index', methods: ['GET'])]
    public function index(LossRepository $lossRepository): Response
    {
        return $this->render('loss/index.html.twig', [
            'losses' => $lossRepository->findBy(
                [],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    #[Route('/loss/new', name: 'app_loss_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        StockService $stockService
    ): Response {
        $loss = new Loss();

        $form = $this->createForm(LossType::class, $loss);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $stockService->registerLoss($loss);

                $this->addFlash(
                    'success',
                    'La perte a été enregistrée et le stock a été mis à jour.'
                );

                return $this->redirectToRoute('app_loss_index');
            } catch (RuntimeException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        return $this->render('loss/new.html.twig', [
            'form' => $form,
        ]);
    }
}
