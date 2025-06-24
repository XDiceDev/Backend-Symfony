<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
    }

    #[Route('/admin', name: 'app_admin')]
    public function admin(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('auth/admin.html.twig', []);
    }

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('auth/home.html.twig', []);
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Пожалуйста, введите корректный email.');
                return $this->redirectToRoute('app_register');
            }

            if (strlen($password) < 6) {
                $this->addFlash('error', 'Пароль должен содержать минимум 6 символов.');
                return $this->redirectToRoute('app_register');
            }

            if ($password !== $passwordConfirm) {
                $this->addFlash('error', 'Пароли не совпадают.');
                return $this->redirectToRoute('app_register');
            }

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Пользователь с таким email уже существует.');
                return $this->redirectToRoute('app_register');
            }

            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Регистрация прошла успешно! Войдите, используя ваш email и пароль.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register.html.twig', []);
    }
}