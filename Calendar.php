<?php

namespace vggalkin\calendar;

use Yii;
use yii\base\Widget;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\base\BaseObject;

class Calendar extends Widget
{
    public $model;
    public $field_table;
    public $link;
    public $birth_date;


    public function init()
    {
        parent::init();

        $this->getView()->registerJs("

         $('.c_ajax').click(function(){
            $.get($(this).attr('href'),
			function(data){
				$('#calendar_borysenko').html(data);
			});
            return false;
         });

        ", View::POS_READY, 'calendar');


        if (!Yii::$app->request->isAjax)
            echo "<div id=\"calendar_borysenko\">" . $this->my_calendar() . "</div>";
        else
            echo $this->my_calendar();

    }


    private function my_calendar()
    {
        $calendar_view = '';

        $month_names = array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
            "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
        if (isset($_GET['y'])) $y = $_GET['y'];
        if (isset($_GET['m'])) $m = $_GET['m'];
        if (isset($_GET['date']) and strstr($_GET['date'], "-")) list($y, $m) = explode("-", $_GET['date']);
        if (!isset($y) or $y < 1970 or $y > 2037) $y = date("Y");
        if (!isset($m) or $m < 1 or $m > 12) $m = date("m");


        $className = $this->model;
        $field_date = $this->field_table;
        $birth_date = $this->birth_date;
        $model = \Yii::createObject($className);
        //$fill = ArrayHelper::getColumn($model::find()->asArray()->where($field_date . '>="'.date('Y-m-d',mktime(0,0,0,$m,1,$y)).'" AND '. $field_date . '<"'.date('Y-m-d',mktime(0,0,0,$m+1,1,$y)).'"')->groupBy($field_date)->all(), $field_date);
        $fill_array = array();
        $holidays = array();
        $birth = '';
        if (isset($_GET['id'])) {
            $Id = $_GET['id'];
            $fills = ArrayHelper::getColumn($model::find()->asArray()->where(['Id' => $Id])->all(), $field_date);
            $birth = ArrayHelper::getColumn($model::find()->asArray()->where(['Id' => $Id])->all(), $birth_date)[0];
            if (!$fills[0] == Null) {
                foreach (explode(',', $fills[0]) as $fill) {
                    array_push($fill_array, $fill);
                }
                foreach ($fill_array as $main_fill) {
                    $listmain = explode(';', $main_fill);
                    $listdate = explode('.', $listmain[0]);
                    $count_holidays = 0;
                    while ($count_holidays < intval($listmain[2])) {
                        array_push($holidays, date("Y-m-d", mktime(0, 0, 0, intval($listdate[1]), intval($listdate[0]) + $count_holidays, intval($listdate[2]))));
                        $count_holidays ++;
                    }
                }
            }
        }
        //var_dump($holidays);
        //var_dump(explode(',', $fill[0]));
        $month_stamp = mktime(0, 0, 0, $m, 1, $y);
        $day_count = date("t", $month_stamp);
        $weekday = date("w", $month_stamp);
        if ($weekday == 0) $weekday = 7;
        $start = -($weekday - 2);
        $last = ($day_count + $weekday - 1) % 7;
        if ($last == 0) $end = $day_count; else $end = $day_count + 7 - $last;
        $today = date("Y-m-d");
        $prev = date('?\m=m&\y=Y', mktime(0, 0, 0, $m - 1, 1, $y));
        $next = date('?\m=m&\y=Y', mktime(0, 0, 0, $m + 1, 1, $y));
        $i = 0;

        if (isset($Id)) {
            $calendar_view .= '<style>
   [data-tooltip] {
    position: relative; /* Относительное позиционирование */ 
   }
   [data-tooltip]::after {
    content: attr(data-tooltip); /* Выводим текст */
    position: absolute; /* Абсолютное позиционирование */
    width: 100px; /* Ширина подсказки */
    left: -70px; top: 0; /* Положение подсказки */
    background: #3989c9; /* Синий цвет фона */
    color: #fff; /* Цвет текста */
    padding: 0.5em; /* Поля вокруг текста */
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3); /* Параметры тени */
    pointer-events: none; /* Подсказка */
    opacity: 0; /* Подсказка невидима */
    z-index: 100; /* Поверх всего
    transition: 1s; /* Время появления подсказки */
   } 
   [data-tooltip]:hover::after {
    opacity: 1; /* Показываем подсказку */
    top: 2em; /* Положение подсказки */
   }
   table.calendar.table-bordered {
   background-color: #d9edf7;
   padding: 10px;
   border-radius: 10px;
   }
  </style>';
            $calendar_view .= '<table width="100%" height="400" border=1 class="calendar table-bordered" cellspacing=1 cellpadding=2>
 <tr>
  <td height="60" class="title" colspan=7>
   <table width="100%" border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td align="left"><a class="c_ajax btn btn-primary" style="margin-left: 10px;" href="' . Url::to('/backend/web/calendar/index' . $prev . '&id=' . $Id) . '">&lt;&lt;&lt;</a></td>
     <td align="center">' . $month_names[$m - 1] . " " . $y . '</td>
     <td align="right"><a class="c_ajax btn btn-primary" style="margin-right: 10px;" href="' . Url::to('/backend/web/calendar/index' . $next . '&id=' . $Id) . '">&gt;&gt;&gt;</a></td>
    </tr>
   </table>
  </td>
 </tr>
 <tr height="30" class="week"><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td style="color: red">Сб</td><td style="color: red">Вс</td></tr>';
        } else {
            $calendar_view .= '<table width="100%" height="400" border=1 class="calendar table-bordered" cellspacing=1 cellpadding=2>
 <tr>
  <td height="60" class="title" colspan=7>
   <table width="100%" border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td align="left"><a class="c_ajax btn btn-primary" style="margin-left: 10px;" href="' . Url::to('/backend/web/calendar/index' . $prev) . '">&lt;&lt;&lt;</a></td>
     <td align="center">' . $month_names[$m - 1] . " " . $y . '</td>
     <td align="right"><a class="c_ajax btn btn-primary" style="margin-right: 10px;" href="' . Url::to('/backend/web/calendar/index' . $next) . '">&gt;&gt;&gt;</a></td>
    </tr>
   </table>
  </td>
 </tr>
 <tr height="30" class="week"><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td style="color: red">Сб</td><td style="color: red">Вс</td></tr>';
        }
        //$birth_date_exp = explode('-', $this->birth_date);

        for ($d = $start; $d <= $end; $d++) {
            if (!($i++ % 7)) $calendar_view .= " <tr>\n";
            $calendar_view .= '  <td align="center">';
            if ($d < 1 or $d > $day_count) {
                $calendar_view .= "&nbsp";
            } else {
                $now = "$y-$m-" . sprintf("%02d", $d);
                $now_2 = explode('-', $now)[1] . explode('-', $now)[2] ;
                $birth_2 = explode('-',$birth)[1] . explode('-', $birth)[2];
                // Сегодняшний день, день рождения и отпуск
                if (is_array($holidays) and in_array($now, $holidays) and $today == $now and $now_2 == $birth_2) {
                    $calendar_view .= '<div class="todate" style="background: linear-gradient(to right, #ff7125f5 33%, #4be079 34%, #4be079 66%, #f18d8d 67%);" data-tooltip="День рождения Отпуск Сегодня"><b>' . $d . '</b></div>';
                }
                // Отпуск и день рождения
                else if (is_array($holidays) and in_array($now, $holidays) and $now_2 == $birth_2) {
                    $calendar_view .= '<div class="todate" style="background: linear-gradient(to right, #ff7125f5 50%, #f18d8d 51%);" data-tooltip="День рождения Отпуск"><b>' . $d . '</b></div>';
                }
                // Отпуск и сегодняшний день
                else if (is_array($holidays) and in_array($now, $holidays) and $today == $now) {
                    $calendar_view .= '<div class="todate" style="background: linear-gradient(to right, #f18d8d 50%, #4be079 51%);" data-tooltip="Отпуск Сегодня"><b>' . $d . '</b></div>';
                }
                // День рождения и сегодняшний день
                else if ($now_2 == $birth_2 and $today == $now) {
                    $calendar_view .= '<div class="todate" style="background: linear-gradient(to right, #ff7125f5 50%, #4be079 51%);" data-tooltip="День рождения Сегодня"><b>' . $d . '</b></div>';
                }
                // Отпуск
                else if (is_array($holidays) and in_array($now, $holidays)) {
                    $calendar_view .= '<div class="todate" style="background-color: #f18d8d;" data-tooltip="Отпуск">' . $d . '</div>';
                }
                // Сегодняшний день
                else if ($today == $now) {
                    $calendar_view .= '<div class="todate" style="background-color: #4be079;" data-tooltip="Сегодня">' . $d . '</div>';
                }
                // День рождения
                else if ($now_2 == $birth_2) {
                    $calendar_view .= '<div class="todate" style="background-color: #ff7125f5;" data-tooltip="День рождения">' . $d . '</div>';
                } else {
                    $calendar_view .= $d;
                }
            }
            $calendar_view .= "</td>\n";
            if (!($i % 7)) $calendar_view .= " </tr>\n";
        }

        $calendar_view .= '</table>';

        return $calendar_view;
    }
}
