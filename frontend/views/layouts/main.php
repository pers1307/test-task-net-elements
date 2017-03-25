<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Генератор v. 2',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        ['label' => 'Импорт',
         'items' => [
             ['label' => 'Импорт товаров',                    'url' => ['/import/index']],
             ['label' => 'Список всех продуктов',             'url' => ['/product/index']],
             ['label' => 'Импорт клиентов',                   'url' => ['/import/clients']],
             ['label' => 'Список всех клиентов',              'url' => ['/client']],
         ],
        ],
        ['label' => 'Генерация заказов',
         'items' => [
             ['label' => 'Выборка продуктов',                 'url' => ['/generate/select']],
             ['label' => 'Распределение продуктов на заказы', 'url' => ['/generate/order']],
             ['label' => 'Добавить отмененные заказы',        'url' => ['/more-orders/cancel']],
             ['label' => 'Добавить заказы для админки',       'url' => ['/more-orders/lost']],
             ['label' => 'Даты продаж',                       'url' => ['/date/sales']],
             ['label' => 'Распределение заказов по датам',    'url' => ['/generate/days']],
             ['label' => 'Даты заказа',                       'url' => ['/date/make-order-day']],
             ['label' => 'Источники совершения заказа',       'url' => ['/order-source/generate']],
             ['label' => 'Распределение клиентов',            'url' => ['/client/spread']],
             ['label' => 'Временные интервалы заказа',        'url' => ['/time/spread-time']],
             ['label' => 'Добавление цены доставки',          'url' => ['/price-delivery/index']],
             ['label' => 'Управленческая таблица',            'url' => ['/order']],
         ],
        ],
        ['label' => 'Звонки и доставки', 'url' => ['/call/index']],
        ['label' => 'Хранилище',
         'items' => [
             ['label' => 'Экспорт',                                  'url' => ['/export/storage']],
             ['label' => 'Данные',                                   'url' => ['/settings/data']],
         ],
        ],
    ];
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>

</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Генератор 2016 - <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>