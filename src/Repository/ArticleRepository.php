<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findByCategorySlug(string $slug, ?string $search = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.categories', 'c')
            ->where('c.slug = :slug')
            ->andWhere('a.published = :published')
            ->setParameter('slug', $slug)
            ->setParameter('published', true)
            ->orderBy('a.createdDateAt', 'DESC');

        if ($search) {
            $qb->andWhere('LOWER(a.title) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
