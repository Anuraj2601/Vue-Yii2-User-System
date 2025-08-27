<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\User;
use yii\web\Response;

class LoginController extends Controller
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

        $behaviors['contentNegotiator']['class'] = \yii\filters\ContentNegotiator::class;
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        return $behaviors;
    }

    public function actionRegister()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::info('Register action called', __METHOD__);
        $request = Yii::$app->request->post();
        Yii::info('Request Data: ' . json_encode($request), __METHOD__);

        $user = new User();
        $user->name = $request['name'] ?? null;
        $user->email = $request['email'] ?? null;
        $user->setPassword($request['password'] ?? '');
        $user->generateAuthKey();
        $user->generateAccessToken();

        $user->language = 'en';  
        $user->image = '/images/profile/sample.jpg';

        if ($user->save()) {
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

    public function actionLogin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request->post(); // parse form-data
        Yii::info('Login Request: ' . json_encode($request), __METHOD__);

        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;

        if (!$email || !$password) {
            return [
                'status' => 'error',
                'message' => 'Email and password are required'
            ];
        }

        $user = User::findOne(['email' => $email]);

        if ($user && $user->validatePassword($password)) {
            $user->generateAccessToken();
            $user->save(false);

            return [
                'status' => 'success',
                'access_token' => $user->access_token,
                'user_name' => $user->name,
                'user_role' => array_keys(Yii::$app->authManager->getRolesByUser($user->id)),
                'user_permissions' => array_keys(Yii::$app->authManager->getPermissionsByUser($user->id))
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Invalid email or password'
        ];
    }
}
