<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;

/**
 * User policy
 *
 * Admins may manage any user account. Regular users may only view and edit
 * their own account, and may not list all users or delete accounts.
 */
class UserPolicy
{
    /**
     * Only admins can list all users.
     */
    public function canIndex(IdentityInterface $user, User $resource): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Admins can add users. (Self-registration is handled separately and skips
     * authorization in the controller.)
     */
    public function canAdd(IdentityInterface $user, User $resource): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Admins can view anyone; users can view their own profile.
     */
    public function canView(IdentityInterface $user, User $resource): bool
    {
        return $this->isAdmin($user) || $this->isSelf($user, $resource);
    }

    /**
     * Admins can edit anyone; users can edit their own profile.
     */
    public function canEdit(IdentityInterface $user, User $resource): bool
    {
        return $this->isAdmin($user) || $this->isSelf($user, $resource);
    }

    /**
     * Only admins can delete users, and they cannot delete their own account.
     */
    public function canDelete(IdentityInterface $user, User $resource): bool
    {
        return $this->isAdmin($user) && !$this->isSelf($user, $resource);
    }

    /**
     * Returns true when the identity has the admin role.
     */
    protected function isAdmin(IdentityInterface $user): bool
    {
        $entity = $user->getOriginalData();

        return $entity instanceof User && $entity->isAdmin();
    }

    /**
     * Returns true when the identity owns the given user record.
     */
    protected function isSelf(IdentityInterface $user, User $resource): bool
    {
        $entity = $user->getOriginalData();

        return $entity instanceof User && $entity->id === $resource->id;
    }
}