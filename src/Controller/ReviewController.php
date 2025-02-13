<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\OpenAiService;

#[Route('/review')]
#[IsGranted('ROLE_ADMIN')]
final class ReviewController extends AbstractController
{
    public function __construct(
        private OpenAiService $openAiService
    ) {}

    #[Route(name: 'app_review_index', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): Response
    {
        return $this->render('review/index.html.twig', [
            'reviews' => $reviewRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_review_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('app_review_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('review/new.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_review_show', methods: ['GET'])]
    public function show(Review $review): Response
    {
        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_review_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_review_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('review/edit.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_review_delete', methods: ['POST'])]
    public function delete(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $review->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($review);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_review_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/check-content', name: 'app_review_check_content', methods: ['POST'])]
    public function checkContent(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('check-content' . $review->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $isAppropriate = $this->openAiService->validateReviewContent($review->getContent());
                
                if (!$isAppropriate) {
                    $author = $review->getAuthor();
                    $author->setRoles(['ROLE_BANNED']);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'L\'utilisateur a été banni pour avoir publié un commentaire inapproprié.');
                } else {
                    $this->addFlash('success', 'Le contenu du commentaire est approprié.');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la vérification : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_review_show', ['id' => $review->getId()], Response::HTTP_SEE_OTHER);
    }
}
