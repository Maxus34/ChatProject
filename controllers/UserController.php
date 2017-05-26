<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 13.12.2016
 * Time: 16:24
 */

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\{ VerbFilter, AccessControl };
use app\models\{ LoginForm, User, RegistrationForm };

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
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
                'class' => VerbFilter::class,
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
        ];
    }


    public function actionRegister(){
        $model = new RegistrationForm();

        if($model->load(Yii::$app->request->post())){

            if ($model->register()){
                Yii::$app->session->setFlash('success', "Registration success."
                    . "<br>A message with instructions to confirm registration has been sent to your email.");

                return $this->goHome();
            } else {

                Yii::$app->session->setFlash('error', 'Form has some errors.');
            }

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


        if (Yii::$app->request->isAjax){
            return $this->renderAjax('login_ajax', [
                'model' => $model,
            ]);

        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }


    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    public function actionConfirmRegistration($id = null, $hash = null){
        if(empty($id) || empty($hash)){
            Yii::$app->session->setFlash('error', "Error has been occured <br> Please try later.");
            return $this->goHome();
        }

        $user = User::findIdentity($id);
        if (empty($user)){
            Yii::$app->session->setFlash('error', "Error: invalid id");
            return $this->redirect(['login']);
        }

        if ($hash == $user->activation_key){
            $user->active = 1;
            $user->save();
            Yii::$app->session->setFlash('success', "Your account has been successfully activated.");

            return $this->redirect(['login']);
        } else {
            echo $user->activation_key;
            echo "<br>" . $hash;
            Yii::$app->session->setFlash('error', "Error: invalid hash");
            return $this->redirect(['login']);
        }
    }
}