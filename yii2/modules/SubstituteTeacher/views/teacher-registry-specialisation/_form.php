<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\SubstituteTeacher\models\TeacherRegistrySpecialisation */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="teacher-registry-specialisation-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'registry_id')->textInput() ?>

    <?= $form->field($model, 'specialisation_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('substituteteacher', 'Create') : Yii::t('substituteteacher', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
