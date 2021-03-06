<?php

use app\modules\finance\Module;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\finance\models\FinanceYear */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="finance-year-form col-lg-3">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'year')->textInput() ?>

    <?= $form->field($model, 'year_credit')->textInput(['maxlength' => true,
                                                        'type' => 'number',
                                                        'min' => "0.00" ,
                                                        'step' => '0.01',
                                                        'style' => 'text-align: left',
                                                        'value' => $model['year_credit']]);


    ?>

    <div class="form-group pull-right">
    	<?= Html::a(Yii::t('app', 'Return'), ['index'], ['class' => 'btn btn-default']) ?>        
        <?= Html::submitButton($model->isNewRecord ? Module::t('modules/finance/app', 'Create') : Module::t('modules/finance/app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>   	    	
    </div>

    <?php ActiveForm::end(); ?>

</div>
