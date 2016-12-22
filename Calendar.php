<?php
namespace borysenko\calendar;

use Yii;
use yii\base\Widget;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\base\Object;

class Calendar extends Widget
{
    public $model;
    public $field_table;

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



        if(!Yii::$app->request->isAjax)
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
        if (isset($_GET['date']) AND strstr($_GET['date'], "-")) list($y, $m) = explode("-", $_GET['date']);
        if (!isset($y) OR $y < 1970 OR $y > 2037) $y = date("Y");
        if (!isset($m) OR $m < 1 OR $m > 12) $m = date("m");


        $className = $this->model;
        $field_date = $this->field_table;
        $model = \Yii::createObject($className);
        $fill = ArrayHelper::getColumn($model::find()->asArray()->where($field_date . '>="'.date('Y-m-d',mktime(0,0,0,$m,1,$y)).'" AND '. $field_date . '<"'.date('Y-m-d',mktime(0,0,0,$m+1,1,$y)).'"')->groupBy($field_date)->all(), $field_date);

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


        $calendar_view .= '<table width="100%" height="400" border=1 class="calendar table-bordered" cellspacing=1 cellpadding=2>
 <tr>
  <td height="60" class="title" colspan=7>
   <table width="100%" border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td align="left"><a class="c_ajax btn btn-primary" href="'. Url::to('/calendar/index'.$prev) .'">&lt;&lt;&lt;</a></td>
     <td align="center">'. $month_names[$m-1]." ".$y .'</td>
     <td align="right"><a class="c_ajax btn btn-primary" href="'.Url::to('/calendar/index'.$next) .'">&gt;&gt;&gt;</a></td>
    </tr>
   </table>
  </td>
 </tr>
 <tr height="30" class="week"><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td>Сб</td><td>Вс</td></tr>';

        for($d=$start;$d<=$end;$d++) {
            if (!($i++ % 7)) $calendar_view .= " <tr>\n";
            $calendar_view .= '  <td align="center">';
            if ($d < 1 OR $d > $day_count) {
                $calendar_view .= "&nbsp";
            } else {
                $now="$y-$m-".sprintf("%02d",$d);
                if (is_array($fill) AND in_array($now,$fill)) {
                    $calendar_view .= '<div class="todate"><a href="'.Url::to(['/news/index','date'=>$now]).'">'.$d.'</a></div>';
                } else {
                    $calendar_view .= $d;
                }
            }
            $calendar_view .= "</td>\n";
            if (!($i % 7))  $calendar_view .= " </tr>\n";
        }

        $calendar_view .='</table>';

        return $calendar_view;
    }
}