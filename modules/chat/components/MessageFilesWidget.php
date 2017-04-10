<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 24.03.2017
 * Time: 18:12
 */

namespace app\modules\chat\components;


use app\models\ImagePlaceholder;
use yii\base\Exception;
use yii\bootstrap\Html;
use yii\bootstrap\Widget;
use app\models\Image;

class MessageFilesWidget extends Widget
{
    public $model;

    protected $files = [];

    public function run(){
        if (empty($this->model))
            throw new Exception('Empty model has been given');

        $this->files = $this->model->files;

        return $this->renderFiles();
    }

    protected function renderFiles(){
        if (isset($this->files['image'])){
            $this->renderImages($this->files['image']);
        }
        if (isset($this->files['audio'])){
            $this->renderAudios($this->files['audio']);
        }

        if (isset($this->files['file'])){
            foreach ($this->files['file'] as $file){
                echo "<a download href='/$file->path'>Download -- $file->name.$file->extension</a>";
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

            echo Html::img($image->getUrl($size),
                [
                    'class'    => 'message-attached-image',
                    'data-url' => $image->getUrl()
                ]);
        }
    }

    protected function renderAudios($array){
        foreach ($array as $file){

            echo  "<div class='message-audio'>"
                . "<p>$file->name</p>"
                . "<audio controls src='/" . $file->path ."'></audio>"
                . "</div>";
        }
    }
}