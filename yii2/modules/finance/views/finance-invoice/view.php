<?php

use app\modules\finance\Module;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\finance\models\FinanceInvoice */

$this->title = Module::t('modules/finance/app', 'View Voucher');
$this->params['breadcrumbs'][] = ['label' => Module::t('modules/finance/app', 'Expenditures Management'), 'url' => ['/finance/default']];
if($expenditures_return == 1)
    $this->params['breadcrumbs'][] = ['label' => Module::t('modules/finance/app', 'Expenditures'), 'url' => ['/finance/finance-expenditure/']];
else
    $this->params['breadcrumbs'][] = ['label' => Module::t('modules/finance/app', 'Vouchers'), 'url' => ['/finance/finance-invoice/']];

$this->params['breadcrumbs'][] = $this->title;
?>
<div class="finance-invoice-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Module::t('modules/finance/app', 'Update'), ['update', 'id' => $model->inv_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Module::t('modules/finance/app', 'Delete'), ['delete', 'id' => $model->inv_id, 'expenditures_return' => $expenditures_return], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Module::t('modules/finance/app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'inv_number',
            'inv_date',
            'inv_order',
            //'inv_deleted',
            'suppl_id',
            //'exp_id',
            'invtype_id',
        ],
    ]) ?>

</div>
