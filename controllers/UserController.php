<?php

namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\User;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class UserController extends Controller
{
    public $enableCsrfValidation = false; // disable CSRF for API

    public function beforeAction($action)
    {
        $language = Yii::$app->request->headers->get('Accept-Language', 'en');
        $supported = ['en', 'de'];
        Yii::$app->language = in_array($language, $supported) ? $language : 'en';

        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['profile', 'index', 'language'],
        ];

        $behaviors['contentNegotiator']['class'] = \yii\filters\ContentNegotiator::class;
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        return $behaviors;
    }

    // Get the authenticated user profile
    public function actionProfile()
    {
        $headers = Yii::$app->request->headers;
        $authHeader = $headers->get('Authorization');

        if (!$authHeader) {
            return [
                'status' => 'error',
                'message' => 'Access token required'
            ];
        }

        Yii::info('Authorization Header: ' . $authHeader, __METHOD__);

        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return [
                'status' => 'error',
                'message' => 'Invalid Authorization header'
            ];
        }

        Yii::info('Extracted Token: ' . $matches[1], __METHOD__);

        $accessToken = trim($matches[1]);
        $user = User::findIdentityByAccessToken($accessToken);

        Yii::info('User found: ' . ($user ? $user->email : 'none'), __METHOD__);

        if (!$user) {
            return ['status' => 'error', 'message' => 'Invalid access token'];
        }

        return [
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->getProfileImageUrl(),
                'role' => array_keys(Yii::$app->authManager->getRolesByUser($user->id)),
                'permissions' => array_keys(Yii::$app->authManager->getPermissionsByUser($user->id)),
                'language' => $user->language,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ];
    }

    public function actionIndex()
    {
        $auth = Yii::$app->authManager;
        $currentUserId = Yii::$app->user->id;

        // Get current user's roles
        $currentUserRoles = $auth->getRolesByUser($currentUserId);
        $currentRoleNames = array_keys($currentUserRoles);

        // Get all users except the current user
        $users = User::find()
            ->where(['!=', 'id', $currentUserId])
            ->all();

        $userData = [];
        foreach ($users as $user) {
            $roles = $auth->getRolesByUser($user->id);
            $roleNames = array_keys($roles);

            if (!empty(array_intersect($roleNames, $currentRoleNames))) {
                continue;
            }

            $userData[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roleNames,
            ];
        }

        return [
            'status' => 'success',
            'users' => $userData,
            'count' => count($userData)
        ];
    }


    public function actionLanguage()
    {
        $request = Yii::$app->request;
        $bodyParams = json_decode($request->rawBody, true);

        $language = $bodyParams['language'] ?? null;

        $user = Yii::$app->user->identity;

        if (!$user) {
            return ['status' => 'error', 'message' => 'User not authenticated'];
        }

        if(!$language) {
            return ['status' => 'error', 'message' => 'Language is required'];
        }

        $user->language = $language;

        if($user->save(false)) {
            return ['status' => 'success', 'message' => 'Language updated', 'language' => $user->language];
        } else {
            return ['status' => 'error', 'message' => 'Failed to update language', 'errors' => $user->getErrors()];
        }
    }

    public function actionUpdateProfile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $headers = Yii::$app->request->headers;
        $authHeader = $headers->get('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return ['status' => 'error', 'message' => 'Invalid or missing access token'];
        }

        $accessToken = trim($matches[1]);
        $user = User::findIdentityByAccessToken($accessToken);

        if (!$user) {
            return ['status' => 'error', 'message' => 'Invalid access token'];
        }

        $request = Yii::$app->request->post();
        $user->name = $request['name'] ?? $user->name;
        $user->email = $request['email'] ?? $user->email;

        // Handle image upload
        $uploadedFile = UploadedFile::getInstanceByName('image');
        if ($uploadedFile) {
            $uploadPath = Yii::getAlias('@app/web/images/profile/');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $fileName = uniqid() . '.' . $uploadedFile->extension;
            $uploadedFile->saveAs($uploadPath . $fileName);

            $user->image = '/images/profile/' . $fileName;
        }

        if ($user->save()) {
            return [
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'language' => $user->language,
                ]
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Update failed',
            'errors' => $user->getErrors()
        ];
    }

    public function actionCreate()
    {
        if(!Yii::$app->user->can('create_user')) {
            throw new Yii\web\ForbiddenHttpException('You are not allowed to create users');
        }

        $request = Yii::$app->request;
        $body = json_decode($request->getRawBody(), true);
        $user = new User();

        $user->name = $body['name'] ?? null;
        $user->email = $body['email'] ?? null;
        $user->setPassword($body['password'] ?? '');
        $user->generateAuthKey();
        $user->generateAccessToken();

        $user->language = 'en';
        $user->image = '/images/profile/sample.jpg';

        if ($user->save()) {
            $auth = Yii::$app->authManager;
            $role = $auth->getRole($body['role']);
            if ($role) {
                $auth->assign($role, $user->id);
            }
            return ['status' => 'success', 'message' => 'User registered successfully'];
        }

        $errors = $user->getErrors();

        
        $formattedErrors = [];
        foreach ($errors as $field => $messages) {
            $formattedErrors[$field] = $messages[0];
        }

        Yii::error($formattedErrors, __METHOD__);

        return [
            'status' => 'error',
            'message' => 'Validation Errors',
            'errors' => $formattedErrors,
        ];
    }

    public function actionUpdate($id) {
        if(!Yii::$app->user->can('edit_user')) {
            throw new Yii\web\ForbiddenHttpException('You are not allowed to edit users');
        }

        $request = Yii::$app->request;
        $user = User::findOne($id);

        if(!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $body = json_decode($request->getRawBody(), true);

        $user->name = $body['name'] ?? $user->name;
        $user->email = $body['email'] ?? $user->email;

        if($request->post('password')) {
            $user->setPassword($body['password'] ?? '');
        }

        if($user->save()) {
            $auth = Yii::$app->authManager;
            $auth->revokeAll($user->id);
            $role = $auth->getRole($body['role']);
            if($role) {
                $auth->assign($role, $user->id);
            }

            return [
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => $user
            ];
        }
        $errors = $user->getErrors();

        
        $formattedErrors = [];
        foreach ($errors as $field => $messages) {
            $formattedErrors[$field] = $messages[0];
        }

        Yii::error($formattedErrors, __METHOD__);

        return [
            'status' => 'error',
            'message' => 'Validation Errors',
            'errors' => $formattedErrors,
        ];
    }

    public function actionDelete($id)
    {
        if(!Yii::$app->user->can('delete_user')) {
            throw new Yii\web\ForbiddenHttpException('You are not allowed to delete users');
        }

        $user = User::findOne($id);

        if (!$user) {
            throw new NotFoundHttpException("User not found");
        }

        if ($user->delete()) {
            return ['status' => 'success', 'message' => 'User deleted successfully'];
        }

        return ['status' => 'error', 'message' => 'Failed to delete user'];
    }

    public function actionRoles()
    {
        $auth = Yii::$app->authManager;
        $allRoles = $auth->getRoles();

        // Get current logged-in user ID
        $userId = Yii::$app->user->id;
        // Get roles assigned to the current user
        $userRoles = array_keys($auth->getRolesByUser($userId));

        $roleList = [];
        foreach ($allRoles as $role) {
            // Only include roles not assigned to current user
            if (!in_array($role->name, $userRoles)) {
                $roleList[] = [
                    'name' => $role->name,
                    'description' => $role->description,
                ];
            }
        }

        return [
            'status' => 'success',
            'roles' => $roleList
        ];
    }


    public function actionEdit($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user = User::findOne($id);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found'
            ];
        }

        $auth = Yii::$app->authManager;

        // Get user's assigned roles
        $userRoles = array_keys($auth->getRolesByUser($user->id));

        // Get all roles
        $allRoles = $auth->getRoles();
        $rolesList = [];
        foreach ($allRoles as $role) {
            if ($role->name !== 'admin') {
                $rolesList[] = [
                    'name' => $role->name,
                    'description' => $role->description,
                ];
            }
        }

        return [
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_role' => $userRoles,
            ],
            'roles' => $rolesList,
        ];
    }

}