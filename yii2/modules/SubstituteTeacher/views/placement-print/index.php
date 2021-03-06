<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\SubstituteTeacher\models\PlacementPrint;
use app\modules\SubstituteTeacher\models\Placement;
use app\components\FilterActionColumn;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\SubstituteTeacher\models\PlacementPrintSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('substituteteacher', 'Placement Prints');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="placement-print-index">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'type',
                'filter' => PlacementPrint::getTypeOptions()
            ],
            [
                'attribute' => 'placement_id',
                'filter' => Placement::defaultSelectables(),
                'value' => function ($m) {
                    return $m->placement->label;
                }
            ],
            [
                'attribute' => 'placement_teacher_id',
                'value' => function ($m) {
                    return $m->placementTeacher ? $m->placementTeacher->teacherBoard->teacher->name . '<br>' . $m->placementTeacher->teacherBoard->label : null;
                },
                'format' => 'html',
                'filter' => false
            ],
            'filename',
            'data:ntext',
            'deleted:boolean',
            'deleted_at:datetime',
            'created_at:datetime',
            'updated_at:datetime',

            [
                'class' => FilterActionColumn::className(),
                'filter' => FilterActionColumn::LINK_INDEX_CONFIRM,
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>
</div>