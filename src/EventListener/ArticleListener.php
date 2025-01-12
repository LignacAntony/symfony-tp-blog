<?php

namespace App\EventListener;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Article::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Article::class)]
class ArticleListener
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(Article $article): void
    {
        $article->setCreatedDateAt(new \DateTimeImmutable());
    }

    public function preUpdate(Article $article): void
    {
        $article->setUpdateDateAt(new \DateTimeImmutable());
    }
}
