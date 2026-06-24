<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    private const MAX_PROFILE_IMAGE_BYTES = 2_097_152; // 2 MB

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
                $this->Flash->success(__('Account created. Please sign in.'));

                return $this->redirect(['action' => 'login']);
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
        $this->Authentication->allowUnauthenticated(['login', 'add', 'publicProfile']);
    }

    /**
     * Public profile page for any user.
     *
     * @param string|null $id Target user id.
     * @return void
     */
    public function publicProfile($id = null): void
    {
        $this->Authorization->skipAuthorization();

        $targetUserId = (int)$id;
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity ? (int)$identity->getIdentifier() : null;

        $user = $this->Users->get($targetUserId, [
            'contain' => [],
        ]);

        $Articles = $this->fetchTable('Articles');
        $articleConditions = [
            'Articles.user_id' => $targetUserId,
            'Articles.published' => true,
        ];
        if ($Articles->getSchema()->hasColumn('silenced')) {
            $articleConditions['Articles.silenced'] = false;
        }

        $myArticles = $Articles->find()
            ->where($articleConditions)
            ->orderDesc('Articles.created')
            ->all()
            ->toArray();

        /** @var \Cake\ORM\Table $Follows */
        $Follows = $this->fetchTable('Follows');

        $followers = $Follows->find()
            ->where(['Follows.following_id' => $targetUserId])
            ->contain(['FollowerUsers'])
            ->orderDesc('Follows.created')
            ->all()
            ->toArray();

        $following = $Follows->find()
            ->where(['Follows.follower_id' => $targetUserId])
            ->contain(['FollowingUsers'])
            ->orderDesc('Follows.created')
            ->all()
            ->toArray();

        $isOwnProfile = $currentUserId !== null && $currentUserId === $targetUserId;
        $isFollowing = false;
        if ($currentUserId !== null && !$isOwnProfile) {
            $isFollowing = $Follows->find()
                ->where([
                    'Follows.follower_id' => $currentUserId,
                    'Follows.following_id' => $targetUserId,
                ])
                ->count() > 0;
        }

        $isPublicProfile = true;
        $this->set(compact(
            'user',
            'myArticles',
            'followers',
            'following',
            'currentUserId',
            'isOwnProfile',
            'isFollowing',
            'isPublicProfile'
        ));
        $this->render('profile');
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

    /**
     * Profile page for the current user.
     * Allows profile image upload and lists user's own posts.
     *
     * @return \Cake\Http\Response|null|void
     */
    public function profile()
    {
        $identity = $this->request->getAttribute('identity');
        $currentUserId = (int)$identity->getIdentifier();

        $user = $this->Users->get($currentUserId, [
            'contain' => [],
        ]);
        $this->Authorization->authorize($user, 'edit');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $upload = $this->request->getData('profile_upload');
            if ($upload instanceof UploadedFileInterface && $upload->getError() === UPLOAD_ERR_OK) {
                $savedPath = $this->saveProfileImage($upload, $user->id);
                if ($savedPath === null) {
                    $this->Flash->error(__('Invalid image file. Use jpg, png, gif, or webp under 2 MB.'));

                    return;
                }
                $user->profile_image = $savedPath;
            }

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your profile has been updated.'));

                return $this->redirect(['action' => 'profile']);
            }

            $this->Flash->error(__('Unable to update your profile.'));
        }

        $Articles = $this->fetchTable('Articles');
        $myArticles = $Articles->find()
            ->where(['Articles.user_id' => $currentUserId])
            ->orderDesc('Articles.created')
            ->all()
            ->toArray();

        /** @var \Cake\ORM\Table $Follows */
        $Follows = $this->fetchTable('Follows');

        $followers = $Follows->find()
            ->where(['Follows.following_id' => $currentUserId])
            ->contain(['FollowerUsers'])
            ->orderDesc('Follows.created')
            ->all()
            ->toArray();

        $following = $Follows->find()
            ->where(['Follows.follower_id' => $currentUserId])
            ->contain(['FollowingUsers'])
            ->orderDesc('Follows.created')
            ->all()
            ->toArray();

        $isOwnProfile = true;
        $isFollowing = false;
        $isPublicProfile = false;

        $this->set(compact(
            'user',
            'myArticles',
            'followers',
            'following',
            'currentUserId',
            'isOwnProfile',
            'isFollowing',
            'isPublicProfile'
        ));
    }

    /**
     * Follow another user.
     *
     * @param string|null $id Target user id.
     * @return \Cake\Http\Response|null
     */
    public function follow($id = null)
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        $currentUserId = (int)$identity->getIdentifier();
        $targetUserId = (int)$id;

        if ($targetUserId <= 0 || $targetUserId === $currentUserId) {
            $this->Flash->error(__('Invalid follow request.'));

            return $this->redirect($this->referer(['controller' => 'Dashboard', 'action' => 'index'], true));
        }

        // Ensure target user exists.
        $this->Users->get($targetUserId);

        /** @var \Cake\ORM\Table $Follows */
        $Follows = $this->fetchTable('Follows');
        $existing = $Follows->find()
            ->where([
                'Follows.follower_id' => $currentUserId,
                'Follows.following_id' => $targetUserId,
            ])
            ->first();

        if ($existing) {
            $this->Flash->success(__('You already follow this user.'));

            return $this->redirect($this->referer(['controller' => 'Dashboard', 'action' => 'index'], true));
        }

        $follow = $Follows->newEntity([
            'follower_id' => $currentUserId,
            'following_id' => $targetUserId,
        ]);

        if ($Follows->save($follow)) {
            $this->Flash->success(__('You are now following this user.'));
        } else {
            $this->Flash->error(__('Could not follow this user.'));
        }

        return $this->redirect($this->referer(['controller' => 'Dashboard', 'action' => 'index'], true));
    }

    /**
     * Unfollow a user.
     *
     * @param string|null $id Target user id.
     * @return \Cake\Http\Response|null
     */
    public function unfollow($id = null)
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        $currentUserId = (int)$identity->getIdentifier();
        $targetUserId = (int)$id;

        /** @var \Cake\ORM\Table $Follows */
        $Follows = $this->fetchTable('Follows');
        $existing = $Follows->find()
            ->where([
                'Follows.follower_id' => $currentUserId,
                'Follows.following_id' => $targetUserId,
            ])
            ->first();

        if (!$existing) {
            $this->Flash->success(__('You are not following this user.'));

            return $this->redirect($this->referer(['controller' => 'Dashboard', 'action' => 'index'], true));
        }

        if ($Follows->delete($existing)) {
            $this->Flash->success(__('Unfollowed.'));
        } else {
            $this->Flash->error(__('Could not unfollow this user.'));
        }

        return $this->redirect($this->referer(['controller' => 'Dashboard', 'action' => 'index'], true));
    }

    /**
     * Persist uploaded profile image under webroot/img/profiles.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $upload Uploaded image.
     * @param int $userId Current user id.
     * @return string|null Relative web path or null on invalid file.
     */
    private function saveProfileImage(UploadedFileInterface $upload, int $userId): ?string
    {
        if ($upload->getSize() === null || $upload->getSize() > self::MAX_PROFILE_IMAGE_BYTES) {
            return null;
        }

        $clientName = (string)$upload->getClientFilename();
        $ext = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        $targetDir = WWW_ROOT . 'img' . DS . 'profiles' . DS;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
        $fullPath = $targetDir . $filename;
        $upload->moveTo($fullPath);

        return 'profiles/' . $filename;
    }
}
