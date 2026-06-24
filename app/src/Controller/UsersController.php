<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->authorize($this->Users->newEmptyEntity());
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['Articles'],
        ]);
        $this->Authorization->authorize($user);

        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->Authorization->skipAuthorization();
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);
        $this->Authorization->authorize($user);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        $this->Authorization->authorize($user);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * BeforeFilter method
     */
    public function beforeFilter(\Cake\Event\EventInterface $event): void
    {
        parent::beforeFilter($event);
        // In UsersController::beforeFilter()
        
        // Configure the login action to not require authentication, preventing
        // the infinite redirect loop issue
       // In UsersController::beforeFilter()
        $this->Authentication->allowUnauthenticated(['login', 'add']);
    }

    /**
     * Login method
     */
    public function login()
    {
        // In the add, login, and logout methods
        $this->Authorization->skipAuthorization();
        $result = $this->Authentication->getResult();
        // If the user is logged in send them away.
        if ($result && $result->isValid()) {
            $identity = $this->request->getAttribute('identity');
            $identityEntity = $identity ? $identity->getOriginalData() : null;
            if ($identityEntity instanceof User && $identityEntity->isBanned()) {
                $this->Authentication->logout();
                $this->Flash->error(__('Your account has been banned. Contact an administrator.'));

                return $this->redirect(['controller' => 'Users', 'action' => 'login']);
            }

            $target = $this->Authentication->getLoginRedirect();
            if (!$target) {
                $target = [
                    'controller' => 'Dashboard',
                    'action' => 'index',
                ];
            }

            return $this->redirect($target);
        }
        if ($this->request->is('post')) {
            $this->Flash->error(__('Invalid username or password'));
        }
    }

    /**
     * Logout method
     */
    public function logout()
    {
        $this->Authorization->skipAuthorization();
        $this->Authentication->logout();
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }
}
