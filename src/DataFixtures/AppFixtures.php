<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Dish;
use App\Entity\DishIngredient;
use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Categories
        $categoryData = [
            'entrees' => ['Entrées', 'Les entrées du restaurant'],
            'plats' => ['Plats principaux', 'Les plats principaux'],
            'pizzas' => ['Pizzas', 'Les différentes pizzas'],
            'desserts' => ['Desserts', 'Les desserts'],
            'boissons' => ['Boissons', 'Les boissons froides et chaudes'],
        ];

        $categories = [];

        foreach ($categoryData as $key => [$name, $description]) {
            $category = new Category();
            $category->setName($name);
            $category->setDescription($description);

            $manager->persist($category);
            $categories[$key] = $category;
        }

        // 2. Ingredients
        // name => [unit, stock quantity, alert threshold]
        $ingredientData = [
            'tomate' => ['Tomate', 'kg', '20.000', '5.000'],
            'fromage' => ['Fromage', 'kg', '15.000', '3.000'],
            'farine' => ['Farine', 'kg', '30.000', '5.000'],
            'poulet' => ['Poulet', 'kg', '12.000', '3.000'],
            'huile' => ['Huile', 'L', '10.000', '2.000'],
            'oignon' => ['Oignon', 'kg', '15.000', '3.000'],
            'pomme_de_terre' => ['Pomme de terre', 'kg', '25.000', '5.000'],
            'lait' => ['Lait', 'L', '12.000', '3.000'],
            'sucre' => ['Sucre', 'kg', '10.000', '2.000'],
            'oeuf' => ['Oeuf', 'pièce', '100.000', '20.000'],
        ];

        $ingredients = [];

        foreach ($ingredientData as $key => [$name, $unit, $stock, $threshold]) {
            $ingredient = new Ingredient();
            $ingredient->setName($name);
            $ingredient->setUnit($unit);
            $ingredient->setStockQuantity($stock);
            $ingredient->setAlertThreshold($threshold);

            $manager->persist($ingredient);
            $ingredients[$key] = $ingredient;
        }

        // 3. Dishes and their recipes
        // recipe quantities use the ingredient's unit
        $dishData = [
            [
                'name' => 'Salade Marocaine',
                'description' => 'Salade fraîche à la marocaine',
                'price' => '25.00',
                'category' => 'entrees',
                'recipe' => [
                    'tomate' => '0.150',
                    'oignon' => '0.050',
                ],
            ],
            [
                'name' => 'Salade au Poulet',
                'description' => 'Salade avec morceaux de poulet',
                'price' => '40.00',
                'category' => 'entrees',
                'recipe' => [
                    'tomate' => '0.100',
                    'poulet' => '0.120',
                ],
            ],
            [
                'name' => 'Poulet Rôti',
                'description' => 'Poulet accompagné de pommes de terre',
                'price' => '75.00',
                'category' => 'plats',
                'recipe' => [
                    'poulet' => '0.250',
                    'pomme_de_terre' => '0.200',
                    'huile' => '0.030',
                ],
            ],
            [
                'name' => 'Omelette au Fromage',
                'description' => 'Omelette préparée avec du fromage',
                'price' => '35.00',
                'category' => 'plats',
                'recipe' => [
                    'oeuf' => '2.000',
                    'fromage' => '0.050',
                ],
            ],
            [
                'name' => 'Pizza Margherita',
                'description' => 'Pizza tomate et fromage',
                'price' => '55.00',
                'category' => 'pizzas',
                'recipe' => [
                    'farine' => '0.200',
                    'tomate' => '0.100',
                    'fromage' => '0.120',
                ],
            ],
            [
                'name' => 'Pizza Poulet',
                'description' => 'Pizza au poulet et fromage',
                'price' => '70.00',
                'category' => 'pizzas',
                'recipe' => [
                    'farine' => '0.200',
                    'poulet' => '0.120',
                    'fromage' => '0.100',
                ],
            ],
            [
                'name' => 'Crêpe Sucrée',
                'description' => 'Crêpe au lait et au sucre',
                'price' => '25.00',
                'category' => 'desserts',
                'recipe' => [
                    'farine' => '0.100',
                    'lait' => '0.150',
                    'sucre' => '0.030',
                    'oeuf' => '1.000',
                ],
            ],
            [
                'name' => 'Gâteau Maison',
                'description' => 'Gâteau préparé au restaurant',
                'price' => '30.00',
                'category' => 'desserts',
                'recipe' => [
                    'farine' => '0.100',
                    'sucre' => '0.050',
                    'oeuf' => '2.000',
                    'lait' => '0.100',
                ],
            ],
        ];

        foreach ($dishData as $data) {
            $dish = new Dish();
            $dish->setName($data['name']);
            $dish->setDescription($data['description']);
            $dish->setSalePrice($data['price']);
            $dish->setIsAvailable(true);
            $dish->setCategory($categories[$data['category']]);

            $manager->persist($dish);

            foreach ($data['recipe'] as $ingredientKey => $quantity) {
                $recipeLine = new DishIngredient();
                $recipeLine->setQuantity($quantity);

                $dish->addIngredient($recipeLine);
                $ingredients[$ingredientKey]->addDishIngredient($recipeLine);

                $manager->persist($recipeLine);
            }
        }

        $manager->flush();
    }
}
