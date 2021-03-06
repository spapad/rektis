<?php 

use yii\bootstrap\Html;

$form_options = [
    'id' => 'email-postman',
];
if ($enable_upload === true) {
    $form_options['enctype'] = 'multipart/form-data';
}
echo Html::beginForm($email_postman_route, 'post', $form_options),
    Html::hiddenInput('envelope', $envelope, []);
?>
<?php if ($enable_upload === true) : ?>
<div style="display: inline-block">
    <label for="attachment" class="btn btn-default" data-toggle='tooltip' title="<strong>Προσθέστε αρχείο για επισύναψη που θα σταλεί με το email</strong>">Αρχείο για επισύναψη</label>
    <?php echo Html::fileInput('attachment', null, ['id' => 'attachment', 'style' => 'display: none']); ?>
</div>
<?php endif; ?>
<?php
echo Html::submitButton("<span class=\"glyphicon glyphicon-send\"></span> {$label}", [
        'class' => 'btn btn-primary',
        'encode' => false,
        'data-toggle' => 'tooltip',
        'title' => (empty($tooltip) ? '' : $tooltip)
    ]),
    Html::endForm();

$js = <<<EOJS
$(function () { 
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]',
        html: true
    });
});
EOJS;
$this->registerJs($js, $this::POS_READY);
