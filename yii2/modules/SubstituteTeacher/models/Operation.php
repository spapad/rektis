<?php
namespace app\modules\SubstituteTeacher\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\FileHelper;
use app\modules\SubstituteTeacher\traits\Selectable;

/**
 * This is the model class for table "{{%stperation}}".
 *
 * @property integer $id
 * @property integer $year
 * @property string $title
 * @property string $description
 * @property string $logo The filename of the logo, relative to the images folder
 * @property string $contract_template
 * @property string $summary_template 
 * @property string $export_template 
 * @property string $decision_template 
 * @property string $created_at
 * @property string $updated_at
 *
 * @property OperationSpecialisation[] $operationSpecialisations
 */
class Operation extends \yii\db\ActiveRecord
{
    use Selectable;

    public $specialisation_ids; // associated specialisations
    public $specialisation_labels;
    public $label;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%stoperation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year', 'title'], 'required'],
            [['year'], 'integer'],
            ['specialisation_ids', 'each', 'rule' => [
                    'exist', 'targetAttribute' => 'id', 'targetClass' => Specialisation::className()
                ]
            ],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'logo', 'contract_template', 'summary_template', 'export_template', 'decision_template', 'description'], 'string', 'max' => 500],
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('substituteteacher', 'ID'),
            'year' => Yii::t('substituteteacher', 'Year'),
            'title' => Yii::t('substituteteacher', 'Title'),
            'description' => Yii::t('substituteteacher', 'Description'),
            'logo' => Yii::t('substituteteacher', 'Logo'),
            'contract_template' => Yii::t('substituteteacher', 'Contact template'),
            'summary_template' => Yii::t('substituteteacher', 'Summary template'),
            'export_template' => Yii::t('substituteteacher', 'Export template'),
            'decision_template' => Yii::t('substituteteacher', 'Decision template'),
            'specialisation_labels' => Yii::t('substituteteacher', 'Specialisation Labels'),
            'specialisation_ids' => Yii::t('substituteteacher', 'Specialisation Ids'),
            'created_at' => Yii::t('substituteteacher', 'Created At'),
            'updated_at' => Yii::t('substituteteacher', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOperationSpecialisations()
    {
        return $this->hasMany(OperationSpecialisation::className(), ['operation_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialisations()
    {
        return $this->hasMany(Specialisation::className(), ['id' => 'specialisation_id'])
                ->via('operationSpecialisations');
    }

    public static function getYearChoices()
    {
        $options = [];
        $year = date('Y') + 1;
        for ($y = 2016; $y < $year; $y++) {
            $options["$y"] = $y;
        }
        return $options;
    }

    public static function getLogoChoices()
    {
        $files = self::filesList(Yii::getAlias("@webroot/images/"), true);
        return array_combine($files, $files);
    }

    public static function getAvailableTemplates()
    {
        return self::filesList(Yii::getAlias("@vendor/admapp/resources/operations/"), false);
    }

    /**
     * Return an alphabetically sorted list of filenames from the designated basedir. 
     * 
     */
    public static function filesList($basedir, $recursive = false)
    {
        $files = FileHelper::findFiles($basedir, ['recursive' => $recursive]);
        if (count($files) > 0) {
            array_walk($files, function (&$item, $key) {
                $item = basename($item);
            });
        }
        sort($files, SORT_STRING);
        return $files;
    }

    /**
     * @inheritdoc
     *
     */
    public function afterFind()
    {
        parent::afterFind();

        $this->label = "{$this->title} ({$this->year})";
        if ($this->specialisations) {
            $this->specialisation_ids = array_map(function ($m) {
                return $m->id;
            }, $this->specialisations);
            $this->specialisation_labels = implode('<br/>', array_map(function ($m) {
                return $m->code . ' ' . $m->name;
            }, $this->specialisations));
        }
    }

    /**
     * Provided a year, get a list of available choices in the form of
     * ID => LABEL suitable for select lists.
     * If year provided is "invalid" all choices are retured.
     *
     * @param int $year
     */
    public static function selectablesForYear($year)
    {
        if (in_array($year, self::getYearChoices(), true)) {
            return static::selectables('id', 'title', 'year', function ($aq) use ($year) {
                return $aq->where(['year' => $year]);
            });
        } else {
            return static::defaultSelectables('id', 'title', 'year');
        }
    }

    public static function defaultSelectables($index_property = 'id', $label_property = 'title', $group_property = 'year')
    {
        return static::selectables($index_property, $label_property, $group_property, null);
    }

    /**
     * @inheritdoc
     * @return OperationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OperationQuery(get_called_class());
    }
}
