<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use App\Model\Entity\User;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Authorization.Authorization');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
        $this->loadComponent('Authentication.Authentication');

    }

    // in src/Controller/AppController.php
public function beforeFilter(\Cake\Event\EventInterface $event): void
{
    parent::beforeFilter($event);

    $identity = $this->request->getAttribute('identity');
    $identityEntity = $identity ? $identity->getOriginalData() : null;
    if ($identityEntity instanceof User && $identityEntity->isBanned()) {
        $this->Authentication->logout();
        $this->Flash->error(__('Your account has been banned. Contact an administrator.'));
        $event->stopPropagation();
        $this->setResponse($this->redirect(['controller' => 'Users', 'action' => 'login']));

        return;
    }

    // for all controllers in our application, make index and view
    // actions public, skipping the authentication check
    $this->Authentication->allowUnauthenticated(['index', 'view']);
}
}
