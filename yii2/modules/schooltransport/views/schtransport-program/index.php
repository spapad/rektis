<?php

use app\modules\schooltransport\Module;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\schooltransport\models\SchtransportProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['label' => Module::t('modules/schooltransport/app', 'School Transportations'), 'url' => ['/schooltransport/default']];
$this->title = Module::t('modules/schooltransport/app', 'Programs');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="schtransport-program-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]);?>


<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'program_title',
            'program_code',
            ['attribute' => 'programcategory_programalias',
                'label' => Module::t('modules/schooltransport/app', 'Program Category'),
            ],
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'urlCreator' => function ($action, $model) {
                    if ($action === 'delete') {
                        $url = Url::to(['/schooltransport/schtransport-program/delete', 'id' =>$model['program_id']]);
                        return $url;
                    }
                    if ($action === 'update') {
                        $url = Url::to(['/schooltransport/schtransport-program/update', 'id' =>$model['program_id']]);
                        return $url;
                    }
                }
            ],
        ]
    ]); ?>
<?php Pjax::end(); ?></div>
