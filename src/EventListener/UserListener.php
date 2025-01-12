<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: User::class)]
class UserListener
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(User $user): void
    {
        $user->setCreatedDateAt(new \DateTimeImmutable());

        $roles = $user->getRoles();
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
            $user->setRoles($roles);
        }
    }
}
