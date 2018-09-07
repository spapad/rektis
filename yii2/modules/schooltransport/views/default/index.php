<?php

use app\modules\schooltransport\Module;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = Module::t('modules/schooltransport/app', 'School Transportations');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="body-content">
    <div class="row">
		<div class="col-lg-4">
            <h3><?= Module::t('modules/schooltransport/app', 'Transportations Approvals');?></h3>
			<p><?= Module::t('modules/schooltransport/app', 'View/create/edit/delete transportations');?></p>
			<p><?= Html::a(Module::t('modules/schooltransport/app', 'Transportations Approvals'), Url::to(['/schooltransport/schtransport-transport']), ['class' => 'btn btn-primary', 'data-method' => 'post']) ?></p>
        </div>
        <div class="col-lg-4">
            <h3><?= Module::t('modules/schooltransport/app', 'School Units');?></h3>
            <p><?= Module::t('modules/schooltransport/app', 'View school units as retrieved from myschool');?></p>
			<p><?= Html::a(Module::t('modules/schooltransport/app', 'School Units'), Url::to(['/schooltransport/schoolunit/']), ['class' => 'btn btn-primary', 'data-method' => 'post']) ?></p>
        </div>
        <div class="col-lg-4">
            <h3><?= Module::t('modules/schooltransport/app', 'Programs');?></h3>
            <p><?= Module::t('modules/schooltransport/app', 'View/edit programs');?></p>
			<p><?= Html::a(Module::t('modules/schooltransport/app', 'Programs'), Url::to(['/schooltransport/schtransport-program']), ['class' => 'btn btn-primary', 'data-method' => 'post']) ?></p>
        </div>        
    </div>
    <div class="row">
            <div class="col-lg-4">
            <h3><?= Module::t('modules/schooltransport/app', 'Transportation Approval States');?></h3>
            <p><?= Module::t('modules/schooltransport/app', 'View/edit states of a transportation approval');?></p>
			<p><?= Html::a(Module::t('modules/schooltransport/app', 'Transportation Approval States'), Url::to(['/schooltransport/schtransport-state']), ['class' => 'btn btn-primary', 'data-method' => 'post']) ?></p>
        </div>        
		<div class="col-lg-4">
            <h3><?= Module::t('modules/schooltransport/app', 'Help');?></h3>
			<p><?= Module::t('modules/schooltransport/app', 'Help regarding the application of the school transportations, the process of creating a school transportation approval as well as the related legislation.');?></p>
			<p><?= Html::a(Module::t('modules/schooltransport/app', 'Help'), Url::to(['/schooltransport/default/help']), ['class' => 'btn btn-primary', 'data-method' => 'post']) ?></p>
        </div>
		<div class="col-lg-4">
            <h3><?= Module::t('modules/schooltransport/app', 'Statistics');?></h3>
			<p><?= Module::t('modules/schooltransport/app', 'View statistics of the carried out school transportations based on the parameters selected.');?></p>
			<p><?= Html::a(Module::t('modules/schooltransport/app', 'Statistics'), Url::to(['/schooltransport/statistic/index']), ['class' => 'btn btn-primary', 'data-method' => 'post']) ?></p>
        </div>        
    </div>    
</div>
