<?php
namespace borysenko\calendar;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\JsExpression;

class BaseController extends Controller{

    public function actionIndex()
    {

        return $this->renderAjax("@vendor/borysenko/calendar/views/calendar.php", []);
    }




}