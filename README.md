<h1>Calendar of events Расширение для Yii 2</h1>
Календарь событий. Обычный календарь с выделенными датами в которых есть события. Перемещение по месяцам сделано на ajax.

<h2>Установка</h2>

<pre>
<code>
php composer.phar require  borysenko/calendar "dev-master"
</code>
</pre>
или
<pre>
<code>
php -d "disable_functions=" composer.phar require  borysenko/calendar "dev-master"
</code>
</pre>

<h2>Настройка</h2>
в \frontend\config\main.php добавляем в самом вверху
<pre>
<code>
\Yii::$container->set('borysenko\calendar\Calendar', [
    'model'=>'frontend\models\News',
    'field_table' => 'date'
]);
</code>
</pre>
model - это модель события. (в моем случае это модель News);
field_table - это поле в таблицы событий, по нему делается выделение дат в каллендаре. (в моем случае это поле date);

в \frontend\config\main.php добавляем
<pre>
<code>
    'controllerMap' => [
        'calendar' => [
            'class' => 'borysenko\calendar\BaseController',
            ]
     ],
</code>
</pre>

<h2>Использование</h2>

В шаблоне:
<pre>
<code>
    echo borysenko\calendar\Calendar::widget([]);
</code>
</pre>
