<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Language;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const MAX_USERS = 10;
    public const MAX_CATEGORY_PER_ARTICLE = 3;
    public const MAX_REVIEWS_PER_ARTICLE = 3;

    public function load(ObjectManager $manager): void
    {
        $users = [];
        $languages = [];
        $categories = [];
        $articles = [];
        $reviews = [];

        $this->createUsers($manager, $users);
        $this->createLanguages($manager, $languages);
        $this->createCategories($manager, $categories);
        $this->createArticles($manager, $articles, $users, $languages);
        $this->createReviews($manager, $reviews, $users, $articles);

        // $this->linkLanguagesToArticles($languages, $articles);
        $this->linkArticlesToCategories($articles, $categories);

        $manager->flush();
    }

    protected function createUsers(ObjectManager $manager, array &$users): void
    {
        for ($i = 0; $i < self::MAX_USERS; $i++) {
            $user = new User();
            $user->setEmail(email: "user_{$i}@example.com");
            $user->setUsername(username: "user_{$i}");
            $user->setPassword(password: 'password');
            $user->setRoles(['ROLE_USER']);
            $users[] = $user;
            $manager->persist(object: $user);
        }

        $admin = new User();
        $admin->setEmail(email: "admin@example.com");
        $admin->setUsername(username: "admin");
        $admin->setPassword('password');
        $admin->setRoles(['ROLE_ADMIN']);
        $users[] = $admin;
        $manager->persist($admin);

        $normal = new User();
        $normal->setEmail(email: "anto@example.com");
        $normal->setUsername(username: "anto");
        $normal->setPassword('password');
        $normal->setRoles(['ROLE_USER']);
        $users[] = $normal;
        $manager->persist($normal);

        $banned = new User();
        $banned->setEmail(email: "hitler@example.com");
        $banned->setUsername(username: "hitler");
        $banned->setPassword('password');
        $banned->setRoles(['ROLE_BANNED']);
        $users[] = $banned;
        $manager->persist($banned);
    }

    protected function createLanguages(ObjectManager $manager, array &$languages): void
    {
        $array = [
            ['code' => 'fr', 'label' => 'Français'],
            ['code' => 'en', 'label' => 'Anglais'],
            ['code' => 'es', 'label' => 'Espagnol'],
            ['code' => 'de', 'label' => 'Allemand'],
            ['code' => 'it', 'label' => 'Italien'],
        ];

        foreach ($array as $element) {
            $language = new Language();
            $language->setCode($element['code']);
            $language->setLabel($element['label']);
            $manager->persist($language);
            $languages[] = $language;
        }
    }

    protected function createCategories(ObjectManager $manager, array &$categories): void
    {
        $array = [
            ['slug' => 'travel', 'name' => 'Voyage'],
            ['slug' => 'sport', 'name' => 'Sport'],
            ['slug' => 'politics', 'name' => 'Politique'],
            ['slug' => 'economy', 'name' => 'Economie'],
            ['slug' => 'culture', 'name' => 'Culture'],
        ];

        foreach ($array as $element) {
            $category = new Category();
            $category->setSlug($element['slug']);
            $category->setName($element['name']);
            $manager->persist($category);
            $categories[] = $category;
        }
    }

    protected function createArticles(ObjectManager $manager, array &$articles, array $authors, array $languages): void
    {
        $array = [
            [
                'title' => 'Voyage en Inde',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'voyage-en-inde',
                'published' => true,
            ],
            [
                'title' => 'Voyage en Chine',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'voyage-en-chine',
                'published' => true,
            ],
            [
                'title' => 'Voyage en Russie',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'voyage-en-russie',
                'published' => true,
            ],
            [
                'title' => 'Voyage en France',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'voyage-en-france',
                'published' => true,
            ],
            [
                'title' => 'Voyage en Italie',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'voyage-en-italie',
                'published' => true,
            ],
            [
                'title' => 'Voyage en Espagne',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'voyage-en-espagne',
                'published' => false,
            ]
        ];

        foreach ($array as $element) {
            $article = new Article();
            $article->setTitle($element['title']);
            $article->setContent($element['content']);
            $article->setSlug($element['slug']);
            $article->setAuthor($authors[array_rand($authors)]);
            $article->setPublished($element['published']);
            $article->setLanguage($languages[array_rand($languages)]);
            $manager->persist($article);
            $articles[] = $article;
        }
    }

    protected function createReviews(ObjectManager $manager, array &$reviews, array $users, array $articles): void
    {
        $contents = [
            [
                'content' => 'Super article',
            ],
            [
                'content' => 'Moyen',
            ],
            [
                'content' => 'Nul',
            ],
        ];

        /** @var Article $article */
        foreach ($articles as $article) {
            for ($i = 0; $i < random_int(1, self::MAX_REVIEWS_PER_ARTICLE); $i++) {
                $review = new Review();
                $review->setContent($contents[array_rand($contents)]['content']);
                $review->setArticle(article: $article);
                $review->setAuthor($users[array_rand($users)]);
                $manager->persist($review);
                $reviews[] = $review;
            }
        }
    }

    protected function linkLanguagesToArticles(array $languages, array $articles): void
    {
        /** @var Article $article */
        foreach ($articles as $article) {
            $randomLanguage = $languages[array_rand($languages)];
            $article->setLanguage($randomLanguage);
        }
    }

    protected function linkArticlesToCategories(array $articles, array $categories): void
    {
        /** @var Article $article */
        foreach ($articles as $article) {
            for ($i = 0; $i < random_int(1, self::MAX_CATEGORY_PER_ARTICLE); $i++) {
                $article->addCategory($categories[array_rand($categories)]);
            }
        }
    }
}
