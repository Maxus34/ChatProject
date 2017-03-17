<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 14.03.2017
 * Time: 12:58
 */

namespace app\modules\admin\components;

use yii\bootstrap\Widget;

class UserSelectPicture extends Widget
{
    /* @attribute $model \app\models\User */

    public $model;
    public $attribute = null;

    public function run()
    {
        $main_image = $this->model->getMainImage();

        return $this->render("@app/modules/admin/components/templates/user_select_picture.php",
            [
                'main_image' => $main_image,
                'attribute'  => $this->attribute,
            ]
        );
    }

}