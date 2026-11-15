<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Form\DishType;
use App\Repository\DishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dish')]
class DishController extends AbstractController
{
    #[Route('/', name: 'app_dish_index', methods: ['GET'])]
    public function index(DishRepository $repository): Response
    {
        return $this->render('dish/index.html.twig', [
            'dishes' => $repository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_dish_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $dish = new Dish();
        $dish->setIsAvailable(true);

        $form = $this->createForm(DishType::class, $dish);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($dish->getIngredients()->isEmpty()) {
                $this->addFlash('danger', 'Ajoutez au moins un ingrédient à la recette.');

                return $this->render('dish/form.html.twig', [
                    'form' => $form,
                    'title' => 'Ajouter un plat',
                ]);
            }

            foreach ($dish->getIngredients() as $recipeItem) {
                $recipeItem->setDish($dish);
            }

            $em->persist($dish);
            $em->flush();

            $this->addFlash('success', 'Plat ajouté avec succès.');

            return $this->redirectToRoute('app_dish_index');
        }

        return $this->render('dish/form.html.twig', [
            'form' => $form,
            'title' => 'Ajouter un plat',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_dish_edit', methods: ['GET', 'POST'])]
    public function edit(
        Dish $dish,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(DishType::class, $dish);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($dish->getIngredients() as $recipeItem) {
                $recipeItem->setDish($dish);
            }

            $em->flush();

            $this->addFlash('success', 'Plat modifié avec succès.');

            return $this->redirectToRoute('app_dish_index');
        }

        return $this->render('dish/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier le plat',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_dish_delete', methods: ['POST'])]
    public function delete(
        Dish $dish,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete-dish-' . $dish->getId(),
            $request->request->get('_token')
        )) {
            if (!$dish->getOrderItems()->isEmpty()) {
                $this->addFlash('danger', 'Impossible de supprimer un plat déjà commandé.');
            } else {
                $em->remove($dish);
                $em->flush();

                $this->addFlash('success', 'Plat supprimé avec succès.');
            }
        }

        return $this->redirectToRoute('app_dish_index');
    }
}
