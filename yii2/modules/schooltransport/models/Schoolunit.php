<?php

namespace app\modules\schooltransport\models;

use app\modules\schooltransport\Module;

/**
 * This is the model class for table "{{%schoolunit}}".
 *
 * @property integer $school_id
 * @property string $school_name
 * @property integer $directorate_id
 *
 * @property Directorate $directorate
 * @property SchtransportTransport[] $schtransportTransports
 */
class Schoolunit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%schoolunit}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['school_name', 'directorate_id'], 'required'],
            [['directorate_id'], 'integer'],
            [['school_name'], 'string', 'max' => 200],
            [['school_name', 'directorate_id'], 'unique', 'targetAttribute' => ['school_name', 'directorate_id'], 'message' => 'The combination of Σχολείο and Διεύθυνση Εκπαίδευσης Σχολείου has already been taken.'],
            [['directorate_id'], 'exist', 'skipOnError' => true, 'targetClass' => Directorate::className(), 'targetAttribute' => ['directorate_id' => 'directorate_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'school_id' => Module::t('modules/schooltransport/app', 'School ID'),
            'school_name' => Module::t('modules/schooltransport/app', 'School'),
            'directorate_id' => Module::t('modules/schooltransport/app', 'Directorate of Education'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDirectorate()
    {
        return $this->hasOne(Directorate::className(), ['directorate_id' => 'directorate_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchtransportTransports()
    {
        return $this->hasMany(SchtransportTransport::className(), ['school_id' => 'school_id']);
    }

    /**
     * @inheritdoc
     * @return SchoolunitQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SchoolunitQuery(get_called_class());
    }
}
