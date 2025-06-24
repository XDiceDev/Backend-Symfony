<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @psalm-suppress MissingOverrideAttribute
 */
class AuthTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testSuccessfulRegistration(): void
    {
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('register')->getValue();

        $client->request('POST', '/register', [
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirm' => 'password123',
            '_csrf_token' => $csrfToken,
        ]);

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-success', 'Регистрация прошла успешно');
    }

    public function testUserLoginAndAccessHome(): void
    {
        $this->createUser('user@example.com', 'password123', ['ROLE_USER']);
        $client = static::createClient();

        $client->request('POST', '/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', 'Добро пожаловать, user@example.com');
    }

    public function testAdminLoginAndAccessAdmin(): void
    {
        $this->createUser('admin@example.com', 'password123', ['ROLE_ADMIN']);
        $client = static::createClient();

        $client->request('POST', '/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $client->followRedirect();
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Админ-панель');
    }

    private function createUser(string $email, string $password, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    }
}