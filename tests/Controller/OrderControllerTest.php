<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()
            ->get(EntityManagerInterface::class);

        $user = new User();

        $user->setEmail(
            'waiter_' . bin2hex(random_bytes(5)) . '@test.com'
        );

        $user->setPassword('test-password-hash');
        $user->setRoles(['ROLE_WAITER']);

        // Enregistrer le User dans la base de données
        $entityManager->persist($user);
        $entityManager->flush();

        // Vérifier que l'utilisateur possède un ID
        self::assertNotNull(
            $user->getId(),
            'Le User doit être enregistré dans la base de données.'
        );

        // Authentification après l'enregistrement
        $client->loginUser($user);

        // Tester la page
        $client->request('GET', '/order');

        self::assertResponseIsSuccessful();
    }
}
