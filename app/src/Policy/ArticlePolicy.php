<?php
namespace App\Policy;

use App\Model\Entity\Article;
use App\Model\Entity\User;
use Authorization\IdentityInterface;

class ArticlePolicy
{
    public function canAdd(IdentityInterface $user, Article $article): bool
    {
        // All logged in users can create articles.
        return true;
    }

    public function canEdit(IdentityInterface $user, Article $article): bool
    {
        // Admins can edit any article; users can edit their own.
        return $this->isAdmin($user) || $this->isAuthor($user, $article);
    }

    public function canDelete(IdentityInterface $user, Article $article): bool
    {
        // Admins can delete any article; users can delete their own.
        return $this->isAdmin($user) || $this->isAuthor($user, $article);
    }

    protected function isAdmin(IdentityInterface $user): bool
    {
        $entity = $user->getOriginalData();

        return $entity instanceof User && $entity->isAdmin();
    }

    protected function isAuthor(IdentityInterface $user, Article $article): bool
    {
        $entity = $user->getOriginalData();

        return isset($entity->id) && $article->user_id === $entity->id;
    }
}