<?php
declare(strict_types=1);

namespace App\Controller;

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

        $this->set('identity', $this->Authentication->getIdentity());
    }
}
