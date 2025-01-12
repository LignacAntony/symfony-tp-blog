<?php

namespace App\Controller;

use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(
        private EntityManagerInterface $entMan
    ) {
        $this->entityManager = $entMan;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route('/reset', name: 'reset_password_page', methods: ['GET'])]
    public function reset(): Response
    {
        return $this->render(view: 'security/reset.html.twig');
    }

    #[Route('/forgot', name: 'forgot_password_page_get', methods: ['GET'])]
    public function forgot_get(): Response
    {
        return $this->render(view: 'security/forgot.html.twig');
    }

    #[Route('/forgot', name: 'forgot_password_page_post', methods: ['POST'])]
    public function forgot_post(
        Request $request,
        UserRepository $user,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator,
    ): Response {
        $email = $request->get('_email');
        if (!$email) {
            $this->addFlash('error', 'Mail non renseigné');
            return $this->redirectToRoute('forgot_password_page_post');
        }

        $user = $user->findOneByEmail($email);
        if (!$user) {
            $this->addFlash('error', 'User not found');
            return $this->redirectToRoute('forgot_password_page_post');
        }

        $token = $tokenGenerator->generateToken();
        $user->setResetToken($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = compact('url', 'user');

        $email_to = (new Email())
            ->from('onboarding@resend.dev')
            ->to($user->getEmail())
            ->subject('Reset password')
            ->text('Sending you the link to reet your password')
            ->html(sprintf(
                '<p>Hello %s</p>
                <p>To reset your password, please click on the following link: <a href="%s">Reset your password</a></p>
                <p>Thank you</p>',
                htmlspecialchars($user->getUsername()),
                htmlspecialchars($url),
            ));

        $mailer->send($email_to);

        return $this->redirectToRoute('app_login');
    }

    #[Route('/forgot/{token}', name: 'reset_password')]
    public function resetPass(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $userRepository->findOneByResetToken($token);

        if (!$user) {
            $this->addFlash('danger', 'Jeton invalide');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setResetToken('');
            $user->setPassword($form->get('password')->getData());
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Password successfully changed');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset.html.twig', [
            'resetForm' => $form->createView()
        ]);
    }

    #[Route('/access-denied', name: 'app_access_denied')]
    public function accessDenied(): Response
    {
        if ($this->isGranted('ROLE_BANNED')) {
            return $this->redirectToRoute('app_banned');
        }
        return $this->render('access_denied.html.twig', [
            'message' => 'You do not have the necessary permissions to access this page.',
        ]);
    }
    #[Route('/banned', name: 'app_banned')]
    #[IsGranted('ROLE_BANNED')]
    public function banned(): Response
    {
        return $this->render('banned.html.twig', [
            'message' => 'You have been banned',
        ]);
    }
}
