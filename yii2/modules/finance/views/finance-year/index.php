<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\finance\models\FinanceYearSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Expenditures Management'), 'url' => ['/finance/default']];
$this->title = Yii::t('app', 'Finance Years');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="finance-year-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create Finance Year'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'year',
            'year_credit',
            'year_lock',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
