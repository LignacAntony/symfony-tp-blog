<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Review;
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
    public function __invoke(Request $request, ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {

        $search = $request->query->get('search', '');

        if ($search) {
            $articles = $articleRepository->createQueryBuilder('a')
                ->where('LOWER(a.title) LIKE LOWER(:search)')
                ->andWhere('a.published = :published')
                ->setParameter('search', '%' . $search . '%')
                ->setParameter('published', true)
                ->orderBy('a.createdDateAt', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $articles = $articleRepository->findBy(['published' => true], ['createdDateAt' => 'DESC']);
        }

        return $this->render('articles.html.twig', [
            'articles' => $articles,
            'categories' => $categoryRepository->findAll(),
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

            return $this->redirectToRoute('app_article_index', ['slug' => $article->getSlug()]);
        }

        return $this->render('article.html.twig', [
            'article' => $article,
            'reviewForm' => $form->createView(),
        ]);
    }

    #[Route('/categories/{slug}', name: 'app_articles_by_category')]
    public function listByCategory(Request $request, string $slug, ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy(['slug' => $slug]);
        $search = $request->query->get('search', '');

        if (!$category) {
            throw $this->createNotFoundException('The category does not exist');
        }

        return $this->render('list.html.twig', [
            'category' => $category,
            'categories' => $categoryRepository->findAll(),
            'articles' => $articleRepository->findByCategorySlug($slug, $search),
        ]);
    }
}
