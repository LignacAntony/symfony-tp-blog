<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Form\ArticlePromptType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\OpenAiService;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use App\Entity\Language;

#[Route('/article')]
#[IsGranted('ROLE_ADMIN')]
final class ArticleController extends AbstractController
{
    public function __construct(
        private OpenAiService $openAiService,
        private LanguageRepository $languageRepository,
        private CategoryRepository $categoryRepository
    ) {}

    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/new/ai', name: 'app_article_new_ai', methods: ['GET', 'POST'])]
    public function newWithAI(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticlePromptType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $prompt = $form->get('prompt')->getData();
                $generatedArticle = $this->openAiService->generateArticle($prompt);

                $article = new Article();
                $article->setTitle($generatedArticle['title']);
                $article->setContent($generatedArticle['content']);
                $article->setLanguage($this->languageRepository->findByCode($generatedArticle['language']));
                foreach ($generatedArticle['categories'] as $categoryName) {
                    if ($category = $this->categoryRepository->findByName($categoryName)) {
                        $article->addCategory($category);
                    }
                }
                $article->setAuthor($this->getUser());
                $article->setPublished(false);
                $article->setSlug($this->createSlug($generatedArticle['title']));

                $entityManager->persist($article);
                $entityManager->flush();

                $this->addFlash('success', 'L\'article a été généré avec succès ! Vous pouvez maintenant le modifier si nécessaire.');
                return $this->redirectToRoute('app_article_edit', ['id' => $article->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la génération de l\'article : ' . $e->getMessage());
            }
        }

        return $this->render('article/new_ai.html.twig', [
            'form' => $form,
        ]);
    }

    private function createSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article, LanguageRepository $languageRepository): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'languages' => $languageRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/translate/{language}', name: 'app_article_translate', methods: ['POST'])]
    public function translate(
        Request $request,
        Article $article,
        Language $language,
        EntityManagerInterface $entityManager,
        OpenAiService $openAiService
    ): Response {
        if (!$this->isCsrfTokenValid('translate' . $article->getId(), $request->request->get('_token'))) {
            throw new InvalidCsrfTokenException('Invalid CSRF token');
        }

        try {
            $translatedContent = $openAiService->translateArticle(
                $article->getTitle(),
                $article->getContent(),
                $language->getLabel()
            );

            $translatedArticle = new Article();
            $translatedArticle->setTitle($translatedContent['title']);
            $translatedArticle->setContent($translatedContent['content']);
            $translatedArticle->setLanguage($language);
            $translatedArticle->setSlug($this->createSlug($translatedContent['title']));
            foreach ($article->getCategories() as $category) {
                $translatedArticle->addCategory($category);
            }
            $translatedArticle->setAuthor($this->getUser());
            $translatedArticle->setPublished(false);

            $entityManager->persist($translatedArticle);
            $entityManager->flush();

            $this->addFlash('success', 'Article traduit avec succès !');
            return $this->redirectToRoute('app_article_show', ['id' => $translatedArticle->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la traduction : ' . $e->getMessage());
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }
    }
}
