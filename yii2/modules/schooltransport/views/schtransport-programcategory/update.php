<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\schooltransport\models\SchtransportProgramcategory */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Schtransport Programcategory',
]) . $model->programcategory_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Schtransport Programcategories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->programcategory_id, 'url' => ['view', 'id' => $model->programcategory_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="schtransport-programcategory-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
