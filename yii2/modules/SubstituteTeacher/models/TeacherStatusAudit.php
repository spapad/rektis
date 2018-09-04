<?php

namespace app\modules\SubstituteTeacher\models;

use Yii;
use yii\helpers\Json;
use yii\db\Expression;
use yii\base\UserException;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "{{%stteacher_status_audit}}".
 *
 * @property integer $id
 * @property integer $teacher_id
 * @property integer $status
 * @property string $audit_ts
 * @property string $audit
 * @property string $data
 * @property string $actor
 * @property array $data_parsed php assoc array of data
 *
 * @property Teacher $teacher
 */
class TeacherStatusAudit extends \yii\db\ActiveRecord
{
    public $data_parsed;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%stteacher_status_audit}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['teacher_id', 'status'], 'integer'],
            [['audit_ts'], 'safe'],
            [['data'], 'string'],
            [['audit'], 'string', 'max' => 80],
            [['teacher_id'], 'exist', 'skipOnError' => true, 'targetClass' => Teacher::className(), 'targetAttribute' => ['teacher_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('substituteteacher', 'ID'),
            'actor' => Yii::t('substituteteacher', 'Audit actor'),
            'teacher_id' => Yii::t('substituteteacher', 'Teacher ID'),
            'status' => Yii::t('substituteteacher', 'Status'),
            'audit_ts' => Yii::t('substituteteacher', 'Audit Ts'),
            'audit' => Yii::t('substituteteacher', 'Audit'),
            'data' => Yii::t('substituteteacher', 'Data'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeacher()
    {
        return $this->hasOne(Teacher::className(), ['id' => 'teacher_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeacherRegistry()
    {
        return $this->hasOne(TeacherRegistry::className(), ['id' => 'registry_id'])
            ->via('teacher');
    }

    /** 
     * Record teacher status change or audit everything...
     * 
     * @param int $teacher_id 
     * @param int $status current teacher status  
     * @param int $audit message a short explanatory text message 
     * @param int $audit_relevant_data an array containing relevant information
     * 
     * @return TeacherStatusAudit On succes the generated audit entry is returned 
     * @throws yii\base\UserException
     */
    public static function audit($teacher_id, $status, $audit_message, $audit_relevant_data) 
    {
        $audit = new TeacherStatusAudit();
        $audit->teacher_id = $teacher_id;
        $audit->status = $status;
        $audit->audit = $audit_message;
        if (empty($audit_relevant_data)) {
            $audit->data = null;
        } else {
            $audit->data = Json::encode($audit_relevant_data);
        }
        $audit->audit_ts = new Expression('NOW()');
        if ($audit->save()) {
            return $audit;
        } else {
            throw new UserException("Error auditing user event"); // TODO log this 
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) { // set on creation 
            $user = \Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
            if (empty($user)) {
                $this->actor = 'UNKNOWN';
            } else {
                $this->actor = "user ";
                if ($identity = $user->getIdentity(false)) {
                    $this->actor .= $identity->getId() . " " . $identity->username;
                }
            }
        }
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();

        if (empty($this->data)) {
            $this->data_parsed = null;
        } else {
            try {
                $this->data_parsed = Json::decode($this->data);
            } catch (InvalidArgumentException $ex) {
                $this->data_parsed = ['UNABLE TO PARSE' => $this->data];
            }
        }
    }

    /**
     * @inheritdoc
     * @return TeacherStatusAuditQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TeacherStatusAuditQuery(get_called_class());
    }
}
