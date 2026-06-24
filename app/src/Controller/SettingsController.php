<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;

/**
 * Settings Controller
 */
class SettingsController extends AppController
{
    /**
     * Settings are personal to the signed-in user, so every action here
     * requires authentication (this clears the public index/view allowance
     * inherited from AppController).
     *
     * @param \Cake\Event\EventInterface $event The beforeFilter event.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([]);
    }

    /**
     * Index method - the Settings page for the current user.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // No model record is edited here (settings belong to the logged-in
        // user themselves), so there's nothing to authorize against.
        $this->Authorization->skipAuthorization();

        $identity = $this->Authentication->getIdentity();
        $identityEntity = $identity ? $identity->getOriginalData() : null;

        if ($this->request->is('post')) {
            $submittedCode = trim((string)$this->request->getData('admin_upgrade_code'));
            $expectedCode = (string)env('ADMIN_UPGRADE_CODE', 'password');

            if (!$identityEntity instanceof User) {
                $this->Flash->error(__('Unable to load your account.'));
            } elseif ($identityEntity->isAdmin()) {
                $this->Flash->success(__('Your account is already an admin account.'));
            } elseif ($submittedCode === '') {
                $this->Flash->error(__('Please enter the admin upgrade code.'));
            } elseif (hash_equals($expectedCode, $submittedCode)) {
                /** @var \App\Model\Table\UsersTable $Users */
                $Users = $this->fetchTable('Users');
                $user = $Users->get((int)$identityEntity->id);
                $user->role = User::ROLE_ADMIN;

                if ($Users->save($user)) {
                    $this->Flash->success(__('Admin access granted. Please re-login to refresh permissions.'));

                    return $this->redirect(['controller' => 'Users', 'action' => 'logout']);
                }

                $this->Flash->error(__('Could not upgrade account role. Please try again.'));
            } else {
                $this->Flash->error(__('Invalid admin upgrade code.'));
            }
        }

        $currentAnnouncement = '';
        $announcementFile = TMP . 'announcement.txt';
        if (file_exists($announcementFile)) {
            $currentAnnouncement = (string)file_get_contents($announcementFile);
        }

        $this->set(compact('identity', 'currentAnnouncement'));
    }

    /**
     * Admin: change any user's role by email.
     */
    public function changeUserRole()
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);
        $this->requireAdmin();

        $email = trim((string)$this->request->getData('target_email'));
        $role  = (string)$this->request->getData('target_role');
        $allowedRoles = [User::ROLE_ADMIN, User::ROLE_USER, User::ROLE_BANNED];

        if ($email === '') {
            $this->Flash->error(__('Please enter a user email address.'));
            return $this->redirect(['action' => 'index']);
        }

        if (!in_array($role, $allowedRoles, true)) {
            $this->Flash->error(__('Invalid role selected.'));
            return $this->redirect(['action' => 'index']);
        }

        /** @var \App\Model\Table\UsersTable $Users */
        $Users = $this->fetchTable('Users');
        $target = $Users->findByEmail($email)->first();

        if (!$target) {
            $this->Flash->error(__('No user found with email "{0}".', $email));
            return $this->redirect(['action' => 'index']);
        }

        // Prevent admins from changing their own role.
        $identity = $this->Authentication->getIdentity();
        $identityEntity = $identity ? $identity->getOriginalData() : null;
        if ($identityEntity instanceof User && (int)$identityEntity->id === (int)$target->id) {
            $this->Flash->error(__('You cannot change your own role here.'));
            return $this->redirect(['action' => 'index']);
        }

        $target->role = $role;
        if ($Users->save($target)) {
            $this->Flash->success(__('Role for {0} updated to "{1}".', $email, $role));
        } else {
            $this->Flash->error(__('Could not update user role. Please try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Admin: publish all unpublished / draft articles.
     */
    public function bulkPublish()
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);
        $this->requireAdmin();

        /** @var \App\Model\Table\ArticlesTable $Articles */
        $Articles = $this->fetchTable('Articles');
        $countFalse = $Articles->updateAll(['published' => true], ['published' => false]);
        $countNull  = $Articles->updateAll(['published' => true], ['published IS' => null]);

        $this->Flash->success(__('Published {0} draft articles.', $countFalse + $countNull));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Admin: set or clear the site-wide announcement banner.
     */
    public function setAnnouncement()
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);
        $this->requireAdmin();

        $message = trim((string)$this->request->getData('announcement'));
        $announcementFile = TMP . 'announcement.txt';

        if ($message === '') {
            if (file_exists($announcementFile)) {
                unlink($announcementFile);
            }
            $this->Flash->success(__('Site announcement cleared.'));
        } else {
            file_put_contents($announcementFile, $message);
            $this->Flash->success(__('Site announcement updated.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Guard: throw 403 if the current identity is not an admin.
     */
    private function requireAdmin(): void
    {
        $identity = $this->Authentication->getIdentity();
        $entity   = $identity ? $identity->getOriginalData() : null;

        if (!$entity instanceof User || !$entity->isAdmin()) {
            throw new ForbiddenException('Admin access required.');
        }
    }
}
