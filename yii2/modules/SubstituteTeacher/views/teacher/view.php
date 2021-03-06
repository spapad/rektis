<?php

use yii\bootstrap\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\SubstituteTeacher\models\Teacher */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('substituteteacher', 'Teachers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="teacher-view">

        <h1>
            <?= Html::encode($this->title) ?>
        </h1>

        <p>
            <?= Html::a(Yii::t('substituteteacher', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a(Yii::t('substituteteacher', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('substituteteacher', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
        </p>

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'registry.name',
                'year',
                [
                    'attribute' => '',
                    'label' => Yii::t('substituteteacher', 'Teacher boards'),
                    'value' => $model->boards ? implode(
                            '<br>',
                            array_map(function ($model) {
                                return $model->label;
                            }, $model->boards)
                        ) : null
                    ,
                    'format' => 'html'
                ],
                [
                    'attribute' => '',
                    'label' => Yii::t('substituteteacher', 'Placement preferences'),
                    'value' => $model->placementPreferences ? implode(
                        '<br>',
                            array_map(function ($pref) {
                                return $pref->label_for_teacher;
                            }, $model->placementPreferences)
                        ) : null
                    ,
                    'format' => 'html'
                ],
                [
                    'attribute' => 'public_experience',
                    'value' => "{$model->public_experience_label} ({$model->public_experience})",
                ],
                [
                    'attribute' => 'smeae_keddy_experience',
                    'value' => "{$model->smeae_keddy_experience_label} ({$model->smeae_keddy_experience})",
                ],
                [
                    'attribute' => 'disability_percentage',
                    'value' => "{$model->disability_percentage}%",
                ],
                'disabled_children',
                'many_children:boolean',
                'three_children:boolean',
            ],
        ]) ?>

    </div>