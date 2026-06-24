<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use Cake\Event\EventInterface;

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

        $this->set(compact('identity'));
    }
}
