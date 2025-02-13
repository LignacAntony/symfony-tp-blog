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
        $this->createArticles($manager, $articles, $users, $languages, $categories);
        $this->createReviews($manager, $reviews, $users, $articles);

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
            $user->setVerified(true);
            $users[] = $user;
            $manager->persist(object: $user);
        }

        $admin = new User();
        $admin->setEmail(email: "admin@example.com");
        $admin->setUsername(username: "admin");
        $admin->setPassword('password');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setVerified(true);
        $users[] = $admin;
        $manager->persist($admin);

        $normal = new User();
        $normal->setEmail(email: "anto@example.com");
        $normal->setUsername(username: "anto");
        $normal->setPassword('password');
        $normal->setRoles(['ROLE_USER']);
        $normal->setVerified(true);
        $users[] = $normal;
        $manager->persist($normal);

        $banned = new User();
        $banned->setEmail(email: "hitler@example.com");
        $banned->setUsername(username: "hitler");
        $banned->setPassword('password');
        $banned->setRoles(['ROLE_BANNED']);
        $banned->setVerified(true);
        $users[] = $banned;
        $manager->persist($banned);
    }

    protected function createLanguages(ObjectManager $manager, array &$languages): void
    {
        $array = [
            ['code' => 'fr', 'label' => 'French'],
            ['code' => 'en', 'label' => 'English'],
            ['code' => 'es', 'label' => 'Spanish'],
            ['code' => 'de', 'label' => 'German'],
            ['code' => 'it', 'label' => 'Italian'],
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
            ['slug' => 'travel', 'name' => 'Travel'],
            ['slug' => 'sport', 'name' => 'Sport'],
            ['slug' => 'politics', 'name' => 'Politics'],
            ['slug' => 'economy', 'name' => 'Economy'],
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

    protected function createArticles(ObjectManager $manager, array &$articles, array $authors, array $languages, array $categories): void
    {
        $array = [
            [
                'title' => 'Travel to India',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'travel-to-india',
                'published' => true,
            ],
            [
                'title' => 'Travel to China',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'travel-to-china',
                'published' => true,
            ],
            [
                'title' => 'Travel to Russia',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'travel-to-russia',
                'published' => true,
            ],
            [
                'title' => 'Travel to France',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'travel-to-france',
                'published' => true,
            ],
            [
                'title' => 'Travel to Italy',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'travel-to-italy',
                'published' => true,
            ],
            [
                'title' => 'Travel to Spain',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies. Nullam nec nisl nec nunc ultricies ultricies.',
                'slug' => 'travel-to-spain',
                'published' => false,
            ],
            [
                'title' => 'The Future of Technology',
                'content' => 'Exploring the advancements and future trends in technology.',
                'slug' => 'future-of-technology',
                'published' => true,
            ],
            [
                'title' => 'The Impact of Sports on Society',
                'content' => 'Analyzing how sports influence various aspects of society.',
                'slug' => 'impact-of-sports',
                'published' => true,
            ],
            [
                'title' => 'Economic Trends in 2023',
                'content' => 'A comprehensive look at the economic trends and predictions for 2023.',
                'slug' => 'economic-trends-2023',
                'published' => true,
            ],
            [
                'title' => 'Cultural Heritage and Preservation',
                'content' => 'Discussing the importance of preserving cultural heritage.',
                'slug' => 'cultural-heritage-preservation',
                'published' => true,
            ],
            [
                'title' => 'Political Landscape in Modern Times',
                'content' => 'Examining the current political landscape and its implications.',
                'slug' => 'political-landscape-modern-times',
                'published' => true,
            ],
            [
                'title' => 'Découvrez l\'histoire et la magie du Stade Santiago Bernabeu à Madrid',
                'content' => 'Le Stade Santiago Bernabeu est l\'un des lieux emblématiques du football mondial, situé à Madrid, en Espagne. Construit en 1947, ce stade est le domicile du célèbre club de football du Real Madrid. Avec une capacité de plus de 81 000 places, le Santiago Bernabeu est non seulement un lieu de compétition sportive, mais aussi un symbole de passion et de tradition pour les fans de football du monde entier.L\'histoire du stade remonte à ses débuts modestes, mais au fil des décennies, il est devenu un véritable temple du football. Le nom du stade rend hommage à l\'une des figures les plus emblématiques de l\'histoire du Real Madrid, Santiago Bernabeu, qui a été président du club pendant de nombreuses années.Lorsque vous visitez le Stade Santiago Bernabeu, vous avez l\'occasion de découvrir l\'histoire riche et prestigieuse du Real Madrid à travers des visites guidées qui vous emmènent dans les coulisses du stade, des vestiaires aux tribunes en passant par le tunnel des joueurs. Vous pouvez également visiter le musée du Real Madrid, qui abrite des trophées, des maillots historiques et d\'autres objets qui retracent l\'histoire glorieuse du club.En plus d\'être le théâtre de matchs de football légendaires, le Santiago Bernabeu est également un lieu de concerts, d\'événements spéciaux et de célébrations. Sa renommée dépasse largement le monde du football, en faisant un lieu incontournable à visiter pour tous les passionnés de sport et de culture.Que vous soyez un fan de football ou simplement un amateur de lieux chargés d\'histoire, le Stade Santiago Bernabeu vous promet une expérience inoubliable. Plongez dans l\'univers fascinant du Real Madrid et du football espagnol en visitant ce joyau architectural et sportif au cœur de Madrid.',
                'slug' => 'decouvrez-l-histoire-et-la-magie-du-stade-santiago-bernabeu-madrid',
                'published' => true,
                'language' => 'fr',
                'category' => ['Sport', 'Culture']
            ],
            [
                'title' => 'Survivre et prospérer dans l\'univers impitoyable de Rust',
                'content' => 'Rust est un jeu de survie en ligne impitoyable qui met à l\'épreuve vos compétences en matière de stratégie, de construction et de combat. Plongez dans un monde ouvert hostile où la coopération est essentielle pour survivre, mais où la trahison et le danger guettent à chaque coin de rue. Dans Rust, vous commencez nu et affamé, avec pour seul objectif de survivre aux éléments, aux animaux sauvages et aux autres joueurs. Construisez des abris, explorez des terres sauvages, fabriquez des armes et formez des alliances pour vous assurer une longévité dans ce monde brutal. Mais méfiez-vous, car dans Rust, la confiance peut être une arme à double tranchant. Les raids, les vols et les escarmouches sont monnaie courante, et seuls les plus rusés et les plus habiles survivront. Avec des graphismes époustouflants, une mécanique de jeu immersive et une communauté passionnée, Rust offre une expérience de jeu unique et palpitante pour les amateurs de survie en ligne. Prêt à relever le défi et à conquérir ce monde brutal ? Alors préparez-vous à plonger dans l\'univers impitoyable de Rust, où seuls les plus forts et les plus rusés survivront.',
                'slug' => 'survivre-et-prosperer-dans-l-univers-impitoyable-de-rust',
                'published' => true,
                'language' => 'fr',
                'category' => ['Culture']
            ],
        ];

        foreach ($array as $element) {
            $article = new Article();
            $article->setTitle($element['title']);
            $article->setContent($element['content']);
            $article->setSlug($element['slug']);
            $article->setAuthor($authors[array_rand($authors)]);
            $article->setPublished($element['published']);
            if (isset($element['language'])) {
                $languageEntity = $languages[array_search($element['language'], array_column($languages, 'code'))];
                $article->setLanguage($languageEntity);
            } else {
                $article->setLanguage($languages[array_rand($languages)]);
            }
            if (isset($element['category'])) {
                foreach ($element['category'] as $category) {
                    $categoryEntity = $categories[array_search($category, array_column($categories, 'name'))];
                    $article->addCategory($categoryEntity);
                }
            }
            $manager->persist($article);
            $articles[] = $article;
        }
    }

    protected function createReviews(ObjectManager $manager, array &$reviews, array $users, array $articles): void
    {
        $contents = [
            [
                'content' => 'Great article, very informative!',
            ],
            [
                'content' => 'Quite average, could be better.',
            ],
            [
                'content' => 'Not good, lacks depth.',
            ],
            [
                'content' => 'Excellent read, highly recommend!',
            ],
            [
                'content' => 'Interesting perspective, well written.',
            ],
            [
                'content' => 'Disappointing, expected more details.',
            ],
            [
                'content' => 'Fantastic insights, learned a lot!',
            ],
            [
                'content' => 'Mediocre content, not engaging.',
            ],
            [
                'content' => 'Very well researched and presented.',
            ],
            [
                'content' => 'Poorly written, needs improvement.',
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
