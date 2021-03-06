<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\TransportFunds */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transport Funds'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transport-funds-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
          //  'id',
            'name',
            'date',
            'year',
            'ada',
          //  'service',
			[
				'label' => $model->getAttributeLabel('service'),
				'value' => $model->service0 ? $model->service0->name  : null
			],       
				'code',
			[
				'label' => Yii::t('app', 'KAE'),
				'attribute' => 'kae'
			], 
            //'kae',
            'amount:currency',
			[
				'label' => $model->getAttributeLabel('count_flag'),
				'value' => Yii::t('app', '{boxstate}', [							
								'boxstate' => ($model->count_flag == 1) ? Yii::t('app', 'YES') : Yii::t('app', 'NO'),
							])
			],         
        ],
    ]) ?>

</div>
