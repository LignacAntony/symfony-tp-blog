<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Review;
use App\Form\ArticleFilterType;
use App\Form\ReviewArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function __invoke(Request $request, ArticleRepository $articleRepository): Response
    {

        $filterForm = $this->createForm(ArticleFilterType::class);
        $filterForm->handleRequest($request);

        $search = $request->query->get('search', '');
        $languageId = $filterForm->get('language')->getData() ? $filterForm->get('language')->getData()->getId() : null;

        $articles = $articleRepository->findByFilters($search, $languageId);

        return $this->render('articles.html.twig', [
            'articles' => $articles,
            'filterForm' => $filterForm->createView(),
        ]);
    }

    #[Route('/articles/{slug}', name: 'app_articles_index', methods: ['GET', 'POST'])]
    public function article(string $slug, Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $article = $articleRepository->findOneBy(['slug' => $slug]);

        if (!$article) {
            throw $this->createNotFoundException('The article does not exist');
        }

        $review = new Review();
        $form = $this->createForm(ReviewArticleType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setArticle($article);
            $review->setAuthor($this->getUser());
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Your review was successfully added.');

            return $this->redirectToRoute('app_articles_index', ['slug' => $article->getSlug()]);
        }

        return $this->render('article.html.twig', [
            'article' => $article,
            'reviewForm' => $form->createView(),
        ]);
    }

    #[Route('/categories/{categorySlug}', name: 'app_articles_by_category')]
    public function listByCategory(Request $request, string $categorySlug, ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy(['slug' => $categorySlug]);
        if (!$category) {
            throw $this->createNotFoundException('The category does not exist');
        }

        $filterForm = $this->createForm(ArticleFilterType::class);
        $filterForm->handleRequest($request);

        $search = $request->query->get('search', '');
        $languageId = $filterForm->get('language')->getData() ? $filterForm->get('language')->getData()->getId() : null;

        $articles = $articleRepository->findByFilters($search, $languageId, $categorySlug);

        return $this->render('list.html.twig', [
            'category' => $category,
            'categories' => $categoryRepository->findAll(),
            'articles' => $articles,
            'filterForm' => $filterForm->createView(),
        ]);
    }
}
