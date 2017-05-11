<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 16.12.2016
 * Time: 23:17
 */

namespace app\modules\admin\controllers;

use app\models\records\UserImageRecord;
use app\models\User;
use app\modules\admin\components\UserSelectPicture;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;

class UserController extends Controller
{
    public function actionIndex(){
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
        ]);
        return $this->render('index', compact('dataProvider'));
    }

    public function actionView($id){
        $user = User::findIdentity($id);

        return $this->render('view', compact('user'));
    }

    public function actionUpdate($id)
    {
        $model = User::findOne($id);
        $model->scenario = User::SCENARIO_UPDATE;

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            $file = UploadedFile::getInstanceByName('main-image');
            if (!empty($file))
                $model -> attachImage($file, true);

            \Yii::$app->session->setFlash('success', "User {$model->id} was updated successfully");
            return $this->redirect(['/admin/user/view', 'id' => $model->id]);
        }

        return $this->render('update', compact('model'));
    }
}