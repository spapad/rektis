<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\finance\Module;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\modules\finance\models\FinanceExpenditure */
/* @var $form yii\widgets\ActiveForm */


?>

<div class="finance-expenditure-form col-lg-6">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'exp_amount')->textInput(['maxlength' => true, 
                                					    'type' => 'number', 
                                					    'min' => "0.00" , 
                                					    'step' => '0.01', 
                                					    'style' => 'text-align: left', 
                                                        'value' => $model['exp_amount']])->label(false); 
    ?>
    
    <?= $form->field($model, 'exp_description')->textInput(['maxlength' => true]); 
    ?>

    <?= $form->field($model, 'suppl_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map($suppliers,'suppl_id', 'suppl_name'),
            'options' => ['placeholder' => Module::t('modules/finance/app', 'Select suppplier...')],
        ]);
    ?>

    <?= $form->field($model, 'fpa_value')->dropDownList(
        ArrayHelper::map($vat_levels,'fpa_value', 'fpa_value'),
        ['prompt'=> Module::t('modules/finance/app', 'VAT')])
    ?>
    <hr />
    <h3><?= Module::t('modules/finance/app', 'Assign withdrawals');?></h3>
    <?php 
        foreach($expendwithdrawals_models as $index => $expendwithdrawals_model){
            echo $form->field($expendwithdrawals_model, "[{$index}]kaewithdr_id")->dropDownList(
                              ArrayHelper::map($kaewithdrawals, 'kaewithdr_id', 'kaewithdr_amount'),
                              ['prompt' => Module::t('modules/finance/app', 'Assign Withdrawal')])->
                              label(false);
        }
    ?>
	<hr />
	<h3><?= Module::t('modules/finance/app', 'Assign deductions');?></h3>
    <?php 
    //echo "<pre>"; print_r($expenddeduction_models); echo "</pre>";
        $index = 0;
        echo $form->field($expenddeduction_models[0], '[0]deduct_id')->radioList(
        [
            $deductions[$index]['deduct_id'] => $deductions[0]['deduct_name'],
            $deductions[++$index]['deduct_id'] => $deductions[1]['deduct_name'],
            $deductions[++$index]['deduct_id'] => $deductions[2]['deduct_name'],
        ],
        ['separator'=>'<br/>']
        )->label(false);
    
        for($i = $index; $i < count($expenddeduction_models); $i++){
            echo $form->field($expenddeduction_models[$i], "[{$i}]deduct_id")->checkbox(['label' => $deductions[$i+1]->deduct_name, 'value' => $deductions[$i+1]->deduct_id]);
        }
    ?>
    <div class="form-group pull-right">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
