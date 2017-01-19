<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%leave}}".
 *
 * @property integer $id
 * @property integer $employee
 * @property integer $type
 * @property integer $decision_protocol
 * @property string $decision_protocol_date
 * @property integer $application_protocol
 * @property string $application_protocol_date
 * @property string $application_date
 * @property string $accompanying_document
 * @property integer $duration
 * @property string $start_date
 * @property string $end_date
 * @property string $reason
 * @property string $comment
 * @property string $extra_reason1
 * @property string $extra_reason2
 * @property string $extra_reason3
 * @property string $create_ts
 * @property string $update_ts
 * @property integer $deleted
 *
 * @property Employee $employeeObj
 * @property LeaveType $typeObj
 * @property LeavePrint[] $leavePrints 
 * @property Leave[] $leavesSameDecision 
 */
class Leave extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%leave}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'create_ts',
                'updatedAtAttribute' => 'update_ts',
                'value' => new Expression('NOW()')
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['employee', 'type', 'decision_protocol', 'application_protocol', 'duration', 'deleted'], 'integer'],
            [['employee', 'type', 'decision_protocol', 'decision_protocol_date', 'application_protocol', 'application_protocol_date', 'application_date', 'duration', 'start_date', 'end_date'], 'required'],
            [['decision_protocol_date', 'application_protocol_date', 'application_date', 'start_date', 'end_date', 'create_ts', 'update_ts'], 'safe'],
            [['comment'], 'string'],
            [['accompanying_document'], 'string', 'max' => 100],
            [['reason'], 'string', 'max' => 200],
            [['extra_reason1', 'extra_reason2', 'extra_reason3'], 'string', 'max' => 250],
            [['employee'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee' => 'id']],
            [['type'], 'exist', 'skipOnError' => true, 'targetClass' => LeaveType::className(), 'targetAttribute' => ['type' => 'id']],
            [['type'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'employee' => Yii::t('app', 'Employee'),
            'type' => Yii::t('app', 'Leave type'),
            'decision_protocol' => Yii::t('app', 'Decision protocol'),
            'decision_protocol_date' => Yii::t('app', 'Decision date'),
            'application_protocol' => Yii::t('app', 'Application protocol'),
            'application_protocol_date' => Yii::t('app', 'Protocol date'),
            'application_date' => Yii::t('app', 'Application date'),
            'accompanying_document' => Yii::t('app', 'Accompanying documents (certifications, etc.'),
            'duration' => Yii::t('app', 'Duration in days'),
            'start_date' => Yii::t('app', 'Start date'),
            'end_date' => Yii::t('app', 'End date'),
            'reason' => Yii::t('app', 'Reason (for special leaves etc.)'),
            'comment' => Yii::t('app', 'Comments'),
            'extra_reason1' => Yii::t('app', 'Extra Reason (included in print)'),
            'extra_reason2' => Yii::t('app', 'Extra Reason (included in print)'),
            'extra_reason3' => Yii::t('app', 'Extra Reason (included in print)'),
            'deleted' => Yii::t('app', 'Deleted'),
            'create_ts' => Yii::t('app', 'Create Ts'),
            'update_ts' => Yii::t('app', 'Update Ts'),
        ];
    }

    /**
     * @return String Leave info str
     */
    public function getInformation()
    {
        return ($this->employeeObj ? $this->employeeObj->fullname : Yii::t('app', 'UNKNOWN'))
                . ' (' . ($this->typeObj ? $this->typeObj->name : Yii::t('app', 'UNKNOWN'))
                . ') ' . Yii::$app->formatter->asDate($this->start_date, 'short')
                . '-' . Yii::$app->formatter->asDate($this->end_date, 'short')
                . '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeObj()
    {
        return $this->hasOne(Employee::className(), ['id' => 'employee']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypeObj()
    {
        return $this->hasOne(LeaveType::className(), ['id' => 'type']);
    }

    /**
     * @return \yii\db\ActiveQuery 
     */
    public function getLeavePrints()
    {
        return $this->hasMany(LeavePrint::className(), ['leave' => 'id']);
    }

    public function allSameDecision()
    {
        return Leave::find()
                        ->where([
                            'decision_protocol' => $this->decision_protocol,
                            'decision_protocol_date' => $this->decision_protocol_date,
                            'type' => $this->type,
                            'deleted' => 0
                           ])
                        ->orderBy('id')
                        ->all();
    }

	/**
	 * @return distinct all emails (employee, service_organic) for all employees of decision-date
	 * deletes duplicate values
	 * deletes empty strings
	 **/
	public function getDecisionEmails()
	{	
		$emails = [];
		$sameDecisionModels = $this->allSameDecision();
        $all_count = count($sameDecisionModels);
        for ($c = 0; $c < $all_count; $c++) {
            $currentModel = $sameDecisionModels[$c];	
            $emails[$c*3] = $currentModel->employeeObj->email;
			$emails[$c*3+1] = $currentModel->employeeObj->serviceOrganic->email;
			$emails[$c*3+2] = $currentModel->employeeObj->serviceServe->email;
        }
        $num = count($emails);
        $k = 0;
        $final_emails = [];
        for ($i = 0; $i < $num; $i++) {
			if (($emails[$i] !== '') && ($emails[$i] !== ' ')) {
				$final_emails[$k] = $emails[$i];
				$k++;
			}
		}	    
        $dist_emails = array_unique($final_emails);      
        $dist_emails = array_diff($dist_emails, ['']);      
        return $dist_emails;
	}

	/**
	 * @return connected Leave IDs for all employees of decision-date
	 **/
	public function getconnectedLeaveIDs()
	{	
		$IDs = [];
		$k = 0; 
		$sameDecisionModels = $this->allSameDecision();
        $all_count = count($sameDecisionModels);
        for ($c = 0; $c < $all_count; $c++) {
            $currentModel = $sameDecisionModels[$c];	
			$IDs[$k] = $currentModel->id;
			$k++;
        }
        return $IDs;
	}
	
	public function getdaysLeft()
	{	
		$total = Yii::$app->db->createCommand(
			' select CASE WHEN daysleft IS NULL THEN 0 ELSE daysleft END AS daysleft from ( ' . 
			'	select employeeID, leaveID, leaveTypeName, leaveLimit, leaveCheck, leaveYear, deleted, duration, case when days is not null then days when days is null then  0 end as days, case when days is not null then (leaveLimit + days - duration) when days is null then (leaveLimit - duration) end as daysleft ' . 
			'	from ' . 
			'	 ( ' . 
			'	SELECT admapp_employee.id AS employeeID, admapp_leave_type.id AS leaveID, admapp_leave_type.name AS leaveTypeName, admapp_leave_type.limit AS leaveLimit, admapp_leave_type.check AS leaveCheck, Year( admapp_leave.start_date ) AS leaveYear, admapp_leave.deleted AS deleted, sum( admapp_leave.duration ) AS duration ' . 
			'	FROM admapp_leave ' . 
			'	LEFT OUTER JOIN admapp_employee ON ( admapp_leave.employee = admapp_employee.id ) , admapp_leave_type ' . 
			'	WHERE admapp_leave.type = admapp_leave_type.id ' . 
			'	AND admapp_employee.id = :id   ' . 
			'	AND admapp_leave.deleted = :del ' . 
			'	AND admapp_leave_type.id = :type ' .		
			'	GROUP BY admapp_employee.id, admapp_leave_type.id, admapp_leave_type.name, admapp_leave_type.limit, admapp_leave_type.check, Year( admapp_leave.start_date ), admapp_leave.deleted  ' . 
			'	 ) AS A  ' . 
			'	LEFT OUTER JOIN  ' . 
			'	 admapp_leave_balance AS B on ( B.employee = A.employeeID AND B.leave_type = A.leaveID and B.year = A.leaveYear - 1 )  ' . 
			' 	WHERE leaveYear = :year ' .
			'	) AS C ', 			
			[
				':id' => $this->employee, 
				':del' => 0,	
				':type' => $this->type, 
				':year' => date("Y", strtotime($this->start_date)), 
			])->queryScalar();
		return $total;
	}

	public function getmydaysLeft($empid, $leavetype, $year)
	{	
		$total = Yii::$app->db->createCommand(
			' select CASE WHEN daysleft IS NULL THEN leaveLimit ELSE daysleft END AS daysleft from ( ' . 
			'	select employeeID, leaveID, leaveTypeName, leaveLimit, leaveCheck, leaveYear, deleted, duration, case when days is not null then days when days is null then  0 end as days, case when days is not null then (leaveLimit + days - duration) when days is null then (leaveLimit - duration) end as daysleft ' . 
			'	from ' . 
			'	 ( ' . 
			'	SELECT admapp_employee.id AS employeeID, admapp_leave_type.id AS leaveID, admapp_leave_type.name AS leaveTypeName, admapp_leave_type.limit AS leaveLimit, admapp_leave_type.check AS leaveCheck, Year( admapp_leave.start_date ) AS leaveYear, admapp_leave.deleted AS deleted, sum( admapp_leave.duration ) AS duration ' . 
			'	FROM admapp_leave ' . 
			'	LEFT OUTER JOIN admapp_employee ON ( admapp_leave.employee = admapp_employee.id ) , admapp_leave_type ' . 
			'	WHERE admapp_leave.type = admapp_leave_type.id ' . 
			'	AND admapp_employee.id = :id   ' . 
			'	AND admapp_leave.deleted = :del ' . 
			'	AND admapp_leave_type.id = :type ' .		
			'	GROUP BY admapp_employee.id, admapp_leave_type.id, admapp_leave_type.name, admapp_leave_type.limit, admapp_leave_type.check, Year( admapp_leave.start_date ), admapp_leave.deleted  ' . 
			'	 ) AS A  ' . 
			'	LEFT OUTER JOIN  ' . 
			'	 admapp_leave_balance AS B on ( B.employee = A.employeeID AND B.leave_type = A.leaveID and B.year = A.leaveYear - 1 )  ' . 
			' 	WHERE leaveYear = :year ' .
			'	) AS C ', 			
			[
				':id' => $empid, 
				':del' => 0,	
				':type' => $leavetype, 
				':year' => $year, 
			])->queryScalar();
		return $total;
	}

    /**
     * @inheritdoc
     * @return LeaveQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LeaveQuery(get_called_class());
    }

}
