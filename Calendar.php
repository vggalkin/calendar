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

        $month_names = array("январь", "февраль", "март", "апрель", "май", "июнь",
            "июль", "август", "сентябрь", "октябрь", "ноябрь", "декабрь");
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

            $calendar_view .= '<h3 style="text-align: center;">Отпуска</h3><table width="100%" height="400" border=1 class="calendar table-bordered" cellspacing=1 cellpadding=2>
 <tr>
  <td height="60" class="title" colspan=7>
   <table width="100%" border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td align="left"><a class="c_ajax btn btn-primary" href="' . Url::to('/backend/web/calendar/index' . $prev . '&id=' . $Id) . '">&lt;&lt;&lt;</a></td>
     <td align="center">' . $month_names[$m - 1] . " " . $y . '</td>
     <td align="right"><a class="c_ajax btn btn-primary" href="' . Url::to('/backend/web/calendar/index' . $next . '&id=' . $Id) . '">&gt;&gt;&gt;</a></td>
    </tr>
   </table>
  </td>
 </tr>
 <tr height="30" class="week"><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td style="color: red">Сб</td><td style="color: red">Вс</td></tr>';
        } else {
            $calendar_view .= '<h3 style="text-align: center;">Отпуска</h3><table width="100%" height="400" border=1 class="calendar table-bordered" cellspacing=1 cellpadding=2>
 <tr>
  <td height="60" class="title" colspan=7>
   <table width="100%" border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td align="left"><a class="c_ajax btn btn-primary" href="' . Url::to('/backend/web/calendar/index' . $prev) . '">&lt;&lt;&lt;</a></td>
     <td align="center">' . $month_names[$m - 1] . " " . $y . '</td>
     <td align="right"><a class="c_ajax btn btn-primary" href="' . Url::to('/backend/web/calendar/index' . $next) . '">&gt;&gt;&gt;</a></td>
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
                $now_2 = explode('-', $now)[1...2];
                $birth_2 = explode('-',$birth)[1...2];
                var_dump($now, $birth);
                if (is_array($holidays) and in_array($now, $holidays) and $today == $now) {
                    $calendar_view .= '<div class="todate" style="background-color: #f18d8d; color: #060064;"><b>' . $d . '</b></div>';
                } else if (is_array($holidays) and in_array($now, $holidays)) {
                    $calendar_view .= '<div class="todate" style="background-color: #f18d8d;">' . $d . '</div>';
                } else if ($today == $now) {
                    $calendar_view .= '<div class="todate" style="background-color: #7bea7b;">' . $d . '</div>';
                } else if ($now_2 == $birth_2) {
                    $calendar_view .= '<div class="todate" style="background-color: #ee5e13;">' . $d . '</div>';
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
