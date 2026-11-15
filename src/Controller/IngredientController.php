<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ingredient')]
class IngredientController extends AbstractController
{
    #[Route('/', name: 'app_ingredient_index', methods: ['GET'])]
    public function index(IngredientRepository $repository): Response
    {
        return $this->render('ingredient/index.html.twig', [
            'ingredients' => $repository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_ingredient_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $ingredient = new Ingredient();

        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ingredient);
            $entityManager->flush();

            $this->addFlash('success', 'Ingrédient ajouté avec succès.');

            return $this->redirectToRoute('app_ingredient_index');
        }

        return $this->render('ingredient/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ingredient_edit', methods: ['GET', 'POST'])]
    public function edit(
        Ingredient $ingredient,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Ingrédient modifié avec succès.');

            return $this->redirectToRoute('app_ingredient_index');
        }

        return $this->render('ingredient/edit.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ingredient_delete', methods: ['POST'])]
    public function delete(
        Ingredient $ingredient,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$ingredient->getId(),
            $request->request->get('_token')
        )) {
            try {
                $entityManager->remove($ingredient);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Ingrédient supprimé avec succès.'
                );
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->addFlash(
                    'danger',
                    'Impossible de supprimer cet ingrédient : il est utilisé dans une recette.'
                );
            }
        }

        return $this->redirectToRoute('app_ingredient_index');
    }
}
