<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 13.12.2016
 * Time: 16:24
 */

namespace app\controllers;

use app\models\RegistrationForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post', 'get'],
                ],
            ],
        ];
    }


    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }


    public function actionRegister(){
        $model = new RegistrationForm();

        if($model->load(Yii::$app->request->post()) && $model->register()){
            Yii::$app->session->setFlash('success', "Регистрация успешна
            <br>На ваш email выслано письмо для подтверждения регистрации");
            return $this->refresh();
        } else {
            Yii::$app->session->setFlash('error',  print_r($model->getErrors()));
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }


    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }


    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    public function actionConfirmRegistration($id = null, $hash = null){
        if(empty($id) || empty($hash)){
            Yii::$app->session->setFlash('error', "Ошибка подтверждения");
            return $this->goHome();
        }
        $user = User::findIdentity($id);
        if (empty($user)){
            Yii::$app->session->setFlash('error', "Ошибка: пользователь не существует");
            return $this->redirect(['login']);
        }

        if ($hash == $user->activation_key){
            $user->active = 1;
            $user->save();
            Yii::$app->session->setFlash('success', "Аккаунт успешно активирован");
            return $this->redirect(['login']);
        } else {
            echo $user->activation_key;
            echo "<br>" . $hash;
            Yii::$app->session->setFlash('error', "Ошибка: ключ подтвеждения неверный");
            return $this->redirect(['login']);
        }
    }
}