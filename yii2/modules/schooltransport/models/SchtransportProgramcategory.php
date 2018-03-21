<?php

namespace app\modules\schooltransport\models;

use Yii;

/**
 * This is the model class for table "{{%schtransport_programcategory}}".
 *
 * @property integer $programcategory_id
 * @property string $programcategory_programtitle
 * @property string $programcategory_programdescription
 * @property integer $programcategory_programparent
 *
 * @property SchtransportProgram[] $schtransportPrograms
 */
class SchtransportProgramcategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%schtransport_programcategory}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['programcategory_programtitle'], 'required'],
            [['programcategory_programparent'], 'integer'],
            [['programcategory_programtitle'], 'string', 'max' => 200],
            [['programcategory_programdescription'], 'string', 'max' => 400],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'programcategory_id' => Yii::t('app', 'Programcategory ID'),
            'programcategory_programtitle' => Yii::t('app', 'Τίτλος Δράσης'),
            'programcategory_programdescription' => Yii::t('app', 'Κωδικός Δράσης'),
            'programcategory_programparent' => Yii::t('app', 'Programcategory Programparent'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchtransportPrograms()
    {
        return $this->hasMany(SchtransportProgram::className(), ['programcategory_id' => 'programcategory_id']);
    }

    /**
     * @inheritdoc
     * @return SchtransportProgramcategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SchtransportProgramcategoryQuery(get_called_class());
    }
}
