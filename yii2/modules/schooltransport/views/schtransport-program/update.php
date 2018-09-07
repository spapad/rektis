<?php

use app\modules\schooltransport\Module;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\schooltransport\models\SchtransportProgram */

$this->params['breadcrumbs'][] = ['label' => Module::t('modules/schooltransport/app', 'School Transportations'), 'url' => ['/schooltransport/default']];
$this->params['breadcrumbs'][] = ['label' => Module::t('modules/schooltransport/app', 'Programs'), 'url' => ['/schooltransport/schtransport-program']];
$this->title = Module::t('modules/schooltransport/app', 'Update program details');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="schtransport-program-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
