<?php

namespace app\modules\SubstituteTeacher\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use PhpOffice\PhpWord\TemplateProcessor;
use yii\helpers\Json;
use yii\web\UnprocessableEntityHttpException;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "admapp_stplacement_print".
 *
 * @property integer $id
 * @property string $type
 * @property integer $placement_id
 * @property integer $placement_teacher_id
 * @property string $filename
 * @property string $data
 * @property integer $deleted
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Placement $placement
 * @property PlacementTeacher $placementTeacher
 */
class PlacementPrint extends \yii\db\ActiveRecord
{
    const PRINT_DELETED = 1;
    const PRINT_NOT_DELETED = 0;

    const TYPE_SUMMARY = 'summary';
    const TYPE_CONTRACT = 'contract';
    const TYPE_DECISION = 'decision';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admapp_stplacement_print';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['deleted'], 'default', 'value' => PlacementPrint::PRINT_NOT_DELETED],
            [['placement_id', 'filename', 'data'], 'required'],
            [['placement_id', 'placement_teacher_id', 'deleted'], 'integer'],
            [['data'], 'string'],
            [['deleted_at', 'created_at', 'updated_at'], 'safe'],
            [['type'], 'string', 'max' => 50],
            [['filename'], 'string', 'max' => 250],
            [['placement_id'], 'exist', 'skipOnError' => true, 'targetClass' => Placement::className(), 'targetAttribute' => ['placement_id' => 'id']],
            [['placement_teacher_id'], 'exist', 'skipOnError' => true, 'targetClass' => PlacementTeacher::className(), 'targetAttribute' => ['placement_teacher_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('substituteteacher', 'ID'),
            'type' => Yii::t('substituteteacher', 'Type'),
            'placement_id' => Yii::t('substituteteacher', 'Placement ID'),
            'placement_teacher_id' => Yii::t('substituteteacher', 'Placement Teacher ID'),
            'filename' => Yii::t('substituteteacher', 'Filename'),
            'data' => Yii::t('substituteteacher', 'Data'),
            'deleted' => Yii::t('substituteteacher', 'Deleted'),
            'deleted_at' => Yii::t('substituteteacher', 'Deleted At'),
            'created_at' => Yii::t('substituteteacher', 'Created At'),
            'updated_at' => Yii::t('substituteteacher', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()')
            ]
        ];
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_CONTRACT => 'Contract',
            self::TYPE_SUMMARY => 'Summary',
            self::TYPE_DECISION => 'Decision',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlacement()
    {
        return $this->hasOne(Placement::className(), ['id' => 'placement_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlacementTeacher()
    {
        return $this->hasOne(PlacementTeacher::className(), ['id' => 'placement_teacher_id']);
    }

    /**
     * Creates a print for the summary of a placement.
     *
     * @param PlacementTeacher $placement_teacher the teacher placement model to use; if not set, use $this->placement_id to locate
     * @param array $placement_related_ids list of placement related ids; if not set they will be retrieved
     * @throws NotFoundHttpException
     */
    protected function fetchPrintInformation($type, $placement_teacher, $placement_related_ids)
    {
        // timestamp filenames
        $dts = date('YmdHis');

        // get teacher placement if not set
        if (empty($placement_teacher)) {
            $placement_teacher = PlacementTeacher::findOne(['placement_id' => $this->placement_id]);
            if (empty($placement_teacher)) {
                throw new NotFoundHttpException(Yii::t('substituteteacher', 'The requested teacher placement does not exist.'));
            }
        }

        // get related ids if not set
        if (empty($placement_related_ids)) {
            $placement_related_ids = Placement::getRelatedIds($placement_teacher->id);
        }

        // get the positions and operations; use first as the one for the templates
        $positions = $placement_teacher->placementPositions;
        $first_position = reset($positions);
        $operation = $first_position->position->operation;
        if ($type === PlacementPrint::TYPE_SUMMARY) {
            $template_filename = $operation->summary_template;
        } elseif ($type === PlacementPrint::TYPE_CONTRACT) {
            $template_filename = $operation->contract_template;
        } elseif ($type === PlacementPrint::TYPE_DECISION) {
            $template_filename = $operation->decision_template;
        } else {
            throw new NotFoundHttpException(Yii::t('substituteteacher', 'The requested placement print type is not recognised.'));
        }

        if (empty($template_filename)) {
            throw new NotFoundHttpException(Yii::t('substituteteacher', 'The requested placement print template does not exist.'));
        }

        return [$dts, $template_filename, $placement_teacher, $placement_related_ids];
    }

    /**
     * Creates a print for this placement.
     * Sets the model filename and data properties.
     * Document type is denoted by property $type
     *
     * @param Placement $placement the placement model to use; if not set, use $this->placement_id to locate
     * @param PlacementTeacher $placement_teacher the teacher placement model to use; if not set, use $this->placement_id to locate
     * @param array $placement_related_ids list of placement related ids; if not set they will be retrieved
     * @throws yii\web\UnprocessableEntityHttpException
     */
    public function generatePrint($placement = null, $placement_teacher = null, $placement_related_ids = null)
    {
        if ($this->type === PlacementPrint::TYPE_SUMMARY) {
            return $this->generateSummaryPrint($placement_teacher, $placement_related_ids);
        } elseif ($this->type === PlacementPrint::TYPE_CONTRACT) {
            return $this->generateContractPrint($placement_teacher, $placement_related_ids);
        } elseif ($this->type === PlacementPrint::TYPE_DECISION) {
            return $this->generateDecisionPrint($placement);
        } else {
            throw new UnprocessableEntityHttpException(Yii::t('substituteteacher', 'The requested document type is not recognised.'));
        }
    }

    public function generateDecisionPrint($placement)
    {
        list($dts, $template_filename, $placement_teacher, $placement_related_ids) = $this->fetchPrintInformation(PlacementPrint::TYPE_DECISION, null, null);

        $filename = sprintf("%s_%08d_%s", $dts, $placement_teacher->id, $template_filename);
        $export_filename = PlacementPrint::getFilenameAbspath($filename, 'export');
        $templateProcessor = new TemplateProcessor(PlacementPrint::getFilenameAbspath($template_filename, 'template'));
        $backup_data = [];

        $templateProcessor->setValue('DATE', \Yii::$app->formatter->asDate($placement->date, 'php:d/m/Y'));
        $templateProcessor->setValue('PROT', $placement->decision);

        $active_placements = $placement->activePlacementTeachers;
        $active_placements_count = count($active_placements);

        $templateProcessor->cloneRow('SN', $active_placements_count);
        foreach ($active_placements as $idx => $placement_teacher) {
            $sn = $idx + 1;
            $registry = $placement_teacher->teacherBoard->teacherRegistry;
            $positions = $placement_teacher->placementPositions; // must be ordered... 

            $data = [
                "SN#{$sn}" => $sn,
                "SURNAME#{$sn}" => $registry->surname,
                "NAME#{$sn}" => $registry->firstname,
                "FATHERNAME#{$sn}" => $registry->fathername,
                "SPECIALISATION#{$sn}" => $registry->surname,
            ];

            $data["PLACEMENT_UNITS_EXTRA#{$sn}"] = '';
            $data["PLACEMENT_HOURS_EXTRA#{$sn}"] = '';
            $first_position = array_shift($positions);
            if (count($positions) > 1) {
                $data["PLACEMENT_UNITS#{$sn}"] = $first_position->position->title;
                $data["PLACEMENT_HOURS#{$sn}"] = $first_position->unified_hours_count;
                foreach ($positions as $pidx => $position) {
                    $i = $pidx + 1;
                    $data["PLACEMENT_UNITS_EXTRA#{$sn}"] .= "{$i}. {$position->position->title} \n<w:br/>";
                    $data["PLACEMENT_HOURS_EXTRA#{$sn}"] .= "{$i}. {$position->unified_hours_count} \n<w:br/>";
                }
            } else {
                $data["PLACEMENT_UNITS#{$sn}"] = $first_position->position->title;
                $data["PLACEMENT_HOURS#{$sn}"] = $first_position->unified_hours_count;
            }

            array_walk($data, function ($v, $k) use ($templateProcessor) {
                $templateProcessor->setValue($k, $v);
            });

            $backup_data[] = $data;
        }

        $templateProcessor->saveAs($export_filename);
        if (!is_readable($export_filename)) {
            throw new NotFoundHttpException(Yii::t('substituteteacher', 'The placement decision document was not generated.'));
        }

        $this->filename = basename($export_filename);
        $this->data = Json::encode($backup_data);
        return true;
    }

    /**
     * Creates a print for the summary of a placement.
     * Sets the model filename and data properties.
     *
     * @param PlacementTeacher $placement_teacher the teacher placement model to use; if not set, use $this->placement_id to locate
     * @param array $placement_related_ids list of placement related ids; if not set they will be retrieved
     * @throws NotFoundHttpException
     */
    public function generateSummaryPrint($placement_teacher = null, $placement_related_ids = null)
    {
        list($dts, $template_filename, $placement_teacher, $placement_related_ids) = $this->fetchPrintInformation(PlacementPrint::TYPE_SUMMARY, $placement_teacher, $placement_related_ids);

        $filename = sprintf("%s_%08d_%s", $dts, $placement_teacher->id, $template_filename);
        $export_filename = PlacementPrint::getFilenameAbspath($filename, 'export');
        $templateProcessor = new TemplateProcessor(PlacementPrint::getFilenameAbspath($template_filename, 'template'));

        $data = [
            'SURNAME' => $placement_teacher->teacherBoard->teacherRegistry->surname,
            'FIRSTNAME' => $placement_teacher->teacherBoard->teacherRegistry->firstname,
            'FATHERNAME' => $placement_teacher->teacherBoard->teacherRegistry->fathername,
            'SPECIALTY' => $placement_teacher->teacherBoard->specialisation->code
        ];
        array_walk($data, function ($v, $k) use ($templateProcessor) {
            $templateProcessor->setValue($k, $v);
        });

        $templateProcessor->saveAs($export_filename);
        if (!is_readable($export_filename)) {
            throw new NotFoundHttpException(Yii::t('substituteteacher', 'The summary document for the teacher placement was not generated.'));
        }

        $this->filename = basename($export_filename);
        $this->data = Json::encode($data);
        return true;
    }

    /**
     * Creates a print for the summary of a placement.
     * Sets the model filename and data properties.
     *
     * @param PlacementTeacher $placement_teacher the teacher placement model to use; if not set, use $this->placement_id to locate
     * @param array $placement_related_ids list of placement related ids; if not set they will be retrieved
     * @throws NotFoundHttpException
     */
    public function generateContractPrint($placement_teacher = null, $placement_related_ids = null)
    {
        list($dts, $template_filename, $placement_teacher, $placement_related_ids) = $this->fetchPrintInformation(PlacementPrint::TYPE_CONTRACT, $placement_teacher, $placement_related_ids);

        $filename = sprintf("%s_%08d_%s", $dts, $placement_teacher->id, $template_filename);
        $export_filename = PlacementPrint::getFilenameAbspath($filename, 'export');
        $templateProcessor = new TemplateProcessor(PlacementPrint::getFilenameAbspath($template_filename, 'template'));

        $data = [
            'SURNAME' => $placement_teacher->teacherBoard->teacherRegistry->surname,
            'FIRSTNAME' => $placement_teacher->teacherBoard->teacherRegistry->firstname,
            'FATHERNAME' => $placement_teacher->teacherBoard->teacherRegistry->fathername,
            'SPECIALTY' => $placement_teacher->teacherBoard->specialisation->code
        ];
        array_walk($data, function ($v, $k) use ($templateProcessor) {
            $templateProcessor->setValue($k, $v);
        });

        $templateProcessor->saveAs($export_filename);
        if (!is_readable($export_filename)) {
            throw new NotFoundHttpException(Yii::t('substituteteacher', 'The contract document for the teacher placement was not generated.'));
        }

        $this->filename = basename($export_filename);
        $this->data = Json::encode($data);
        return true;
    }

    /**
     * Return the filename, with absolute path, to the designated file. 
     * $type denotes which base path to use:
     * - 'export' for print documents 
     * - 'template' for export templates 
     * 
     * @param string $filename The basename of the file; if it contains dir information it will be stripped. 
     * @param string $type Denotes which basepath to user.
     * @return null|string The filename with fulll path info. 
     */
    public static function getFilenameAbspath($filename, $type)
    {
        $filename = basename($filename);
        if ($type === 'export') {
            return Yii::getAlias("@vendor/admapp/exports/operations/{$filename}");
        } elseif ($type === 'template') {
            return Yii::getAlias("@vendor/admapp/resources/operations/{$filename}");
        } else {
            return null;
        }
    }
    /**
     * @inheritdoc
     * @return PlacementPrintQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PlacementPrintQuery(get_called_class());
    }
}
