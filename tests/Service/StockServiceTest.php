<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Dish;
use App\Entity\DishIngredient;
use App\Entity\Ingredient;
use App\Entity\OrderItem;
use App\Entity\RestaurantOrder;
use App\Entity\StockMovement;
use App\Service\StockService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StockServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->entityManager = static::getContainer()
            ->get(EntityManagerInterface::class);

        // Transaction externe pour isoler chaque test.
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();

        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        parent::tearDown();
    }

    #[Test]
    public function it_deducts_stock_when_order_is_served(): void
    {
        [$order, $ingredient] = $this->createOrderScenario();

        $service = new StockService($this->entityManager);

        $service->serveOrder($order);

        self::assertSame('9.000', $ingredient->getStockQuantity());
        self::assertSame('served', $order->getStatus());
        self::assertNotNull($order->getServedAt());
        self::assertNotNull($order->getStockDeductedAt());

        $movements = $this->entityManager
            ->getRepository(StockMovement::class)
            ->findBy(['ingredient' => $ingredient]);

        self::assertCount(1, $movements);
        self::assertSame('OUT_ORDER', $movements[0]->getType());
        self::assertSame('1.000', $movements[0]->getQuantity());
    }

    #[Test]
    public function it_prevents_deducting_stock_twice(): void
    {
        [$order, $ingredient] = $this->createOrderScenario();

        $service = new StockService($this->entityManager);

        $service->serveOrder($order);

        try {
            $service->serveOrder($order);
            self::fail('A second stock deduction should not be allowed.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString(
                'stock',
                strtolower($exception->getMessage())
            );
        }

        self::assertSame('9.000', $ingredient->getStockQuantity());

        $movements = $this->entityManager
            ->getRepository(StockMovement::class)
            ->findBy(['ingredient' => $ingredient]);

        self::assertCount(1, $movements);
    }

    #[Test]
    public function it_rejects_order_when_stock_is_insufficient(): void
    {
        [$order, $ingredient] = $this->createOrderScenario(
            stockQuantity: '0.500'
        );

        $service = new StockService($this->entityManager);

        try {
            $service->serveOrder($order);
            self::fail('An order with insufficient stock should be rejected.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString(
                'Stock insuffisant',
                $exception->getMessage()
            );
        }

        self::assertSame('0.500', $ingredient->getStockQuantity());
        self::assertSame('pending', $order->getStatus());

        $movements = $this->entityManager
            ->getRepository(StockMovement::class)
            ->findBy(['ingredient' => $ingredient]);

        self::assertCount(0, $movements);
    }

    #[Test]
    public function it_rejects_cancelled_order(): void
    {
        [$order, $ingredient] = $this->createOrderScenario(
            status: 'cancelled'
        );

        $service = new StockService($this->entityManager);

        try {
            $service->serveOrder($order);
            self::fail('A cancelled order should not be served.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString(
                'annul',
                strtolower($exception->getMessage())
            );
        }

        self::assertSame('10.000', $ingredient->getStockQuantity());
        self::assertSame('cancelled', $order->getStatus());

        $movements = $this->entityManager
            ->getRepository(StockMovement::class)
            ->findBy(['ingredient' => $ingredient]);

        self::assertCount(0, $movements);
    }

    /**
     * Creates a complete order with one dish and one ingredient.
     *
     * @return array{RestaurantOrder, Ingredient}
     */
    private function createOrderScenario(
        string $stockQuantity = '10.000',
        string $recipeQuantity = '0.500',
        int $orderQuantity = 2,
        string $status = 'pending'
    ): array {
        $category = new Category();
        $category->setName('Test Category');

        $ingredient = new Ingredient();
        $ingredient->setName('Test Ingredient');
        $ingredient->setUnit('kg');
        $ingredient->setStockQuantity($stockQuantity);
        $ingredient->setAlertThreshold('1.000');

        $dish = new Dish();
        $dish->setName('Test Dish');
        $dish->setDescription('Dish for automated tests');
        $dish->setSalePrice('50.00');
        $dish->setIsAvailable(true);
        $dish->setCategory($category);

        $dishIngredient = new DishIngredient();
        $dishIngredient->setQuantity($recipeQuantity);
        $dishIngredient->setDish($dish);
        $dishIngredient->setIngredient($ingredient);

        $dish->addIngredient($dishIngredient);
        $ingredient->addDishIngredient($dishIngredient);

        $order = new RestaurantOrder();
        $order->setStatus($status);
        $order->setTableNumber('TEST-1');

        $orderItem = new OrderItem();
        $orderItem->setQuantity($orderQuantity);
        $orderItem->setUnitPrice('50.00');
        $orderItem->setDish($dish);
        $orderItem->setRestaurantOrder($order);

        $order->addItem($orderItem);

        // Persist every entity explicitly.
        $this->entityManager->persist($category);
        $this->entityManager->persist($ingredient);
        $this->entityManager->persist($dish);
        $this->entityManager->persist($dishIngredient);
        $this->entityManager->persist($order);
        $this->entityManager->persist($orderItem);

        $this->entityManager->flush();

        return [$order, $ingredient];
    }
}
