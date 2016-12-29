<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\db\ActiveRecord;
use admapp\Validators\VatNumberValidator;
use yii\data\SqlDataProvider;

/**
 * This is the model class for table "{{%employee}}".
 *
 * @property integer $id
 * @property integer $status
 * @property string $name
 * @property string $surname
 * @property string $fathersname
 * @property string $mothersname
 * @property string $tax_identification_number
 * @property string $email
 * @property string $telephone
 * @property string $mobile
 * @property string $address
 * @property string $identity_number
 * @property string $social_security_number
 * @property integer $specialisation
 * @property string $identification_number
 * @property string $serve_desicion
 * @property string $serve_decision_date
 * @property string $serve_decision_subject
 * @property string $appointment_fek
 * @property string $appointment_date
 * @property integer $service_organic
 * @property integer $service_serve
 * @property integer $position
 * @property string $rank
 * @property string $rank_date
 * @property integer $pay_scale
 * @property string $pay_scale_date
 * @property string $service_adoption
 * @property string $service_adoption_date
 * @property string $work_base
 * @property string $home_base
 * @property integer $master_degree
 * @property integer $doctorate_degree
 * @property string $work_experience
 * @property string $comments
 * @property string $iban
 * @property integer $deleted
 * @property string $create_ts
 * @property string $update_ts
 *
 * @property EmployeeStatus $status0
 * @property Specialisation $specialisation0
 * @property Service $serviceOrganic
 * @property Service $serviceServe
 * @property Position $position0
 * @property Leave[] $leaves
 */
class Employee extends \yii\db\ActiveRecord
{
	public $leaveSumDelFlag = 0; // Για τα σύνολα των αδειών αν θα βγαίνουν για τις μη διεγραμμένες (0) ή τις διεγραμμένες (1)
	public $transportSumDelFlag = 0; // Για τα σύνολα των μετακινήσεων αν θα βγαίνουν για τις μη διεγραμμένες (0) ή τις διεγραμμένες (1)

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%employee}}';
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
            [['status', 'specialisation', 'service_organic', 'service_serve', 'position', 'pay_scale', 'master_degree', 'doctorate_degree', 'work_experience', 'deleted'], 'integer'],
            [['name', 'surname', 'fathersname', 'tax_identification_number', /*'social_security_number',*/ 'identification_number', /*'appointment_fek', 'appointment_date',*/ 'rank', 'pay_scale'/*, 'service_adoption_date'*/], 'required'],
            [['tax_identification_number'], 'string', 'max' => 9],
            [['iban'], 'string', 'max' => 27],
            [['tax_identification_number'], VatNumberValidator::className(), 'allowEmpty' => true],
            ['email', 'email'],
            [['appointment_date', 'rank_date', 'pay_scale_date', 'service_adoption_date', 'serve_decision_date', 'create_ts', 'update_ts'], 'safe'],
            [['comments'], 'string'],
            [['name', 'surname', 'fathersname', 'mothersname', 'email'], 'string', 'max' => 100],
            [['telephone', 'serve_decision', 'work_base', 'home_base', 'mobile', 'identity_number', 'social_security_number'], 'string', 'max' => 40],
            [['address', 'serve_decision_subject'], 'string', 'max' => 200],
            [['identification_number', 'appointment_fek', 'service_adoption'], 'string', 'max' => 10],
            [['rank'], 'string', 'max' => 4],
            [['identification_number'], 'unique'],
            [['identity_number'], 'unique'],
            [['master_degree', 'doctorate_degree', 'work_experience'], 'default', 'value' => 0],
            [['identity_number'],'default'],
            [['social_security_number'], 'integer'],
            [['social_security_number'], 'string', 'length' => 11],
            [['identification_number'], 'integer'],
            [['identification_number'], 'string', 'length' => 6],
            [['position'], 'exist', 'skipOnError' => true, 'targetClass' => Position::className(), 'targetAttribute' => ['position' => 'id']],
            [['service_organic'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_organic' => 'id']],
            [['service_serve'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_serve' => 'id']],
            [['specialisation'], 'exist', 'skipOnError' => true, 'targetClass' => Specialisation::className(), 'targetAttribute' => ['specialisation' => 'id']],
            [['status'], 'exist', 'skipOnError' => true, 'targetClass' => EmployeeStatus::className(), 'targetAttribute' => ['status' => 'id']],
            // use filter to avoid getting attributes marked as dirty (changed)
            [['status','specialisation','service_organic','service_serve','position','pay_scale','master_degree','doctorate_degree','work_experience'], 'filter', 'filter' => 'intval']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => Yii::t('app', 'Status'),
            'name' => Yii::t('app', 'Name'),
            'surname' => Yii::t('app', 'Surname'),
            'fathersname' => Yii::t('app', 'Father\'s name'),
            'mothersname' => Yii::t('app', 'Mother\'s name'),
            'tax_identification_number' => Yii::t('app', 'TIN'),
            'email' => 'Email',
            'telephone' => Yii::t('app', 'Telephone'),
            'mobile' => Yii::t('app', 'Mobile'),
            'address' => Yii::t('app', 'Address'),
            'identity_number' => Yii::t('app', 'Identity Number'),
            'social_security_number' => Yii::t('app', 'Social Security Number'),
            'specialisation' => Yii::t('app', 'Specialisation'),
            'identification_number' => Yii::t('app', 'Identification Number'),
            'iban' => Yii::t('app', 'IBAN'),
            'appointment_fek' => Yii::t('app', 'Appointment FEK'),
            'appointment_date' => Yii::t('app', 'Appointment Date'),
            'service_organic' => Yii::t('app', 'Service Organic'),
            'service_serve' => Yii::t('app', 'Service Serve'),
            'position' => Yii::t('app', 'Position'),
            'rank' => Yii::t('app', 'Rank'),
            'rank_date' => Yii::t('app', 'Rank Date'),
            'pay_scale' => Yii::t('app', 'Pay Scale'),
            'pay_scale_date' => Yii::t('app', 'Pay Scale Date'),
            'service_adoption' => Yii::t('app', 'Service Adoption'),
            'service_adoption_date' => Yii::t('app', 'Service Adoption Date'),
            'serve_decision' => Yii::t('app', 'Service Decision'),
            'serve_decision_date' => Yii::t('app', 'Service Decision Date'),
            'serve_decision_subject' => Yii::t('app', 'Service Decision Subject'),
            'work_base' => Yii::t('app', 'Work base'),
            'home_base' => Yii::t('app', 'Home base'),           
            'master_degree' => Yii::t('app', 'No of Master Degrees'),
            'doctorate_degree' => Yii::t('app', 'No of Doctorate Degrees'),
            'work_experience' => Yii::t('app', 'Work Experience'),
            'comments' => Yii::t('app', 'Comments'),
            'create_ts' => 'create ts',
            'update_ts' => 'update ts',
        ];
    }

    public function ranksList()
    {	// associative array ώστε και η τιμή στα select αλλά και η τιμή στη βάση να είναι το αλφαριθμητικό που βλέπω
		// αν αποφασίσουμε να κρατάμε στη βάση κωδικούς 0..5 αντί Α..ΣΤ απλά το ξανακάνω απλό array
		// return ['ΣΤ', 'Ε', 'Δ', 'Γ', 'Β', 'Α'];
        return ['Α'=>'Α', 'Β'=>'Β', 'Γ'=>'Γ', 'Δ' =>'Δ', 'Ε' =>'Ε', 'ΣΤ' => 'ΣΤ'];
    }

    public function payscaleList()
    {
        return [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19];
    }

    public function getFullname()
    {
        return $this->name . ' ' . $this->surname;
    }

    public function getRank0()
    {
        if ($this->rank)
            return $this->ranksList()[$this->rank];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus0()
    {
        return $this->hasOne(EmployeeStatus::className(), ['id' => 'status']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialisation0()
    {
        return $this->hasOne(Specialisation::className(), ['id' => 'specialisation']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceOrganic()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_organic']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceServe()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_serve']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosition0()
    {
        return $this->hasOne(Position::className(), ['id' => 'position']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeaves()
    {
        return $this->hasMany(Leave::className(), ['employee' => 'id'])->orderBy(['start_date' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransports()
    {
        return $this->hasMany(Transport::className(), ['employee' => 'id'])->orderBy(['start_date' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeavesDuration()
    {
        return $this->hasMany(Leave::className(), ['employee' => 'id'])->where(['deleted' => 0])->sum('duration');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportsDuration()
    {
        return $this->hasMany(Transport::className(), ['employee' => 'id'])->where(['deleted' => 0])->sum('days_applied');
    }
  
    /**
     * @inheritdoc
     * @return EmployeeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EmployeeQuery(get_called_class());
    }

	/*return */
	public function getCountLeavesTotals()
	{
		$total = Yii::$app->db->createCommand(
					' select count(*) ' .
					' from ( select admapp_employee.id as id ' .
							' FROM admapp_leave 	 LEFT OUTER JOIN admapp_employee ON (admapp_leave.employee = admapp_employee.id) ,  admapp_leave_type  ' .
							' WHERE admapp_leave.type = admapp_leave_type.id  ' .
							' AND admapp_employee.id = :id  ' .
							' AND admapp_leave.deleted = :del  ' .
							' GROUP BY admapp_employee.id, admapp_leave_type.name, Year(admapp_leave.start_date), admapp_leave.deleted ) as e  ' .
					' group by e.id ',
					 [':id' => $this->id, 
					':del' => $this->leaveSumDelFlag])->queryScalar();
		return $total;
	}

	/*return DataProvider*/
	public function getLeavesTotals()
	{
		return new SqlDataProvider([
				'sql' => ' select admapp_employee.id as employeeID, ' .
					' admapp_leave_type.name as leaveTypeName , ' .
					' Year(admapp_leave.start_date) as leaveYear, ' .
					' admapp_leave.deleted as deleted, ' .
					' sum(admapp_leave.duration) as duration ' . 
					' FROM admapp_leave ' .
					' LEFT OUTER JOIN admapp_employee ON (admapp_leave.employee = admapp_employee.id) ,' .
					' admapp_leave_type ' .
					' WHERE admapp_leave.type = admapp_leave_type.id ' .
					' AND admapp_employee.id = :id ' .
					' AND admapp_leave.deleted = :del ' .
					' GROUP BY admapp_employee.id, admapp_leave_type.name, Year(admapp_leave.start_date), admapp_leave.deleted' .
					' ORDER BY Year(admapp_leave.start_date) DESC, admapp_leave_type.name ASC '	,
				'params' => [
					':id' => $this->id, 
					':del' => $this->leaveSumDelFlag,	
				],
		]);
	}

	/**
     * @return \yii\db\ActiveQuery
     */
 /*   public function getLeavesDurationByType($myYear, $LeaveType)
    {  // Παράμετροι ο τύπος άδειας και η χρονιά, π.χ. $LeaveType = 10;  $myYear = date("Y"); 
        return $this->hasMany(Leave::className(), ['employee' => 'id'])
		->where(['deleted' => 0])
		->andWhere(['type' => $LeaveType])
		->andWhere(['YEAR(start_date)' =>  $myYear])
		->sum('duration');
    } */
   
   // Override beforeSave() to log employee table changes to log target
   public function beforeSave($insert)
   {
      if (parent::beforeSave($insert)) {
         if ($dirty = $this->getDirtyAttributes())
         {
            $out = Yii::$app->user->identity->username . ' has modified ' . $this->surname . ' ' . $this->name . ' (id: ' . $this->id . '): ';
            foreach($dirty as $k => $v) {
               if ($k == 'update_ts')
                  continue;
               $out .= $k . ' from ' .$this->getOldAttribute($k) . ' to ' . $v . ', ';
            }
            Yii::info($out,'employee');
         }
         return true;
      }
   }
   
	/*return */
	public function getCountTransportTotals()
	{
		$total = Yii::$app->db->createCommand(
					' select count(*) ' .
					' from ( select admapp_employee.id as id ' .
							' FROM admapp_transport LEFT OUTER JOIN admapp_employee ON (admapp_transport.employee = admapp_employee.id) ,  admapp_transport_type  ' .
							' WHERE admapp_transport.type = admapp_transport_type.id  ' .
							' AND admapp_employee.id = :id  ' .
							' AND admapp_transport.deleted = :del  ' .
							' GROUP BY admapp_employee.id, admapp_transport_type.name, Year(admapp_transport.start_date), admapp_transport.deleted ) as e  ' .
					' group by e.id ',
					 [':id' => $this->id, 
					':del' => $this->transportSumDelFlag])->queryScalar();
		return $total;
	}

	/*return DataProvider*/
	public function getTransportsTotals()
	{
		return new SqlDataProvider([
				'sql' => ' select admapp_employee.id as employeeID, ' .
					' admapp_transport_type.name as transportTypeName , ' .
					' Year(admapp_transport.start_date) as transportYear, ' .
					' admapp_transport.deleted as deleted, ' .
					' sum(admapp_transport.days_applied) as duration ' . 
					' FROM admapp_transport ' .
					' LEFT OUTER JOIN admapp_employee ON (admapp_transport.employee = admapp_employee.id) ,' .
					' admapp_transport_type ' .
					' WHERE admapp_transport.type = admapp_transport_type.id ' .
					' AND admapp_employee.id = :id ' .
					' AND admapp_transport.deleted = :del ' .
					' GROUP BY admapp_employee.id, admapp_transport_type.name, Year(admapp_transport.start_date), admapp_transport.deleted' .
					' ORDER BY Year(admapp_transport.start_date) DESC, admapp_transport_type.name ASC '	,
				'params' => [
					':id' => $this->id, 
					':del' => $this->transportSumDelFlag,	
				],
		]);
	}

	/*return */
	public function getTransportTypeTotal($empid, $typeid, $year)
	{
		$total = Yii::$app->db->createCommand(
			' select duration from ( ' .
				' select admapp_employee.id as employeeID, ' .
					' admapp_transport_type.name as transportTypeName , ' .
					' Year(admapp_transport.start_date) as transportYear, ' .
					' admapp_transport.deleted as deleted, ' .
					' sum(admapp_transport.days_applied) as duration ' . 
				' FROM admapp_transport ' .
					' LEFT OUTER JOIN admapp_employee ON (admapp_transport.employee = admapp_employee.id) ,' .
					' admapp_transport_type ' .
				' WHERE admapp_transport.type = admapp_transport_type.id ' .
					' AND admapp_employee.id = :id ' .
					' AND admapp_transport.type = :type ' .
					' AND YEAR(admapp_transport.start_date) = :year ' .
					' AND admapp_transport.deleted = :del ' .
				' GROUP BY admapp_employee.id, admapp_transport_type.name, Year(admapp_transport.start_date), admapp_transport.deleted ) l',
					 [':id' => $empid, 
					 ':type' => $typeid,
					 ':year' => $year,
					':del' => 0])->queryScalar();
		return $total;
	}
   
}
