<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 24.03.2017
 * Time: 18:12
 */

namespace app\modules\chat\components;


use app\models\ImagePlaceholder;
use yii\bootstrap\Widget;
use app\models\Image;

class MessageFilesWidget extends Widget
{
    public $model;

    protected $files = [];

    public function run(){
        if (empty($this->model))
            return;

        $this->files = $this->model->files;

        return $this->renderFiles();
    }

    protected function renderFiles(){
        if (isset($this->files['image'])){
            $this->renderImages($this->files['image']);
        }
        if (isset($this->files['audio'])){
            foreach ($this->files['audio'] as $file){

                echo "$file->name";
                echo "<audio controls src='/" . $file->path ."'></audio>";
            }
        }
    }

    protected function renderImages($array){
        $size = [300, null];

        if (count($array) > 1){
            $size = [null, 150];
        }


        foreach ($this->files['image'] as $file){
            $image = new Image($file->path  );
            echo "<image src='" . $image->getUrl($size) ."'>";
        }
    }

}