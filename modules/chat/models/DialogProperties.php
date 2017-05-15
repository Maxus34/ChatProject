<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 10.02.2017
 * Time: 22:34
 */

namespace app\modules\chat\models;

use yii\base\Model;

class DialogProperties extends Model
{
    public $id;
    public $title;
    public $users;

    public function rules(){
        return [
            [ ['id'], 'integer' ],
            [ ['title'], 'string', 'max' =>  20],
            [ ['users'], 'default', 'value' => []],
        ];
    }

    public function attributeLabels() {
        return [
            'title' => "Title",
            'users' => "Users"
        ];
    }
}