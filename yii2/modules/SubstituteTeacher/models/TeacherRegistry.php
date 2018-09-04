<?php

namespace app\modules\SubstituteTeacher\models;

use Yii;
use app\modules\SubstituteTeacher\traits\Selectable;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%stteacher_registry}}".
 *
 * @property integer $id
 * @property string $gender
 * @property string $surname
 * @property string $firstname
 * @property string $fathername
 * @property string $mothername
 * @property string $marital_status
 * @property integer $protected_children
 * @property string $mobile_phone
 * @property string $home_phone
 * @property string $work_phone
 * @property string $home_address
 * @property string $city
 * @property string $postal_code
 * @property string $social_security_number
 * @property string $tax_identification_number
 * @property string $tax_service
 * @property string $identity_number
 * @property string $bank
 * @property string $iban
 * @property string $email
 * @property string $birthdate
 * @property string $birthplace
 * @property integer $aei
 * @property integer $tei
 * @property integer $epal
 * @property integer $iek
 * @property integer $military_service_certificate
 * @property integer $sign_language
 * @property integer $braille
 * @property string $comments
 * @property string $ama
 * @property string $efka_facility
 * @property string $municipality
 * @property string $created_at
 * @property string $updated_at 
 * @property string $passport_number

 *
 * @property Teacher[] $teachers
 * @property Specialisation[] $specialisations
 * @property TeacherRegistrySpecialisation[] $teacherRegistrySpecialisations
 */
class TeacherRegistry extends \yii\db\ActiveRecord
{
    use Selectable;

    const GENDER_FEMALE = 'F';
    const GENDER_MALE = 'M';
    const GENDER_OTHER = 'O';

    const MARITAL_STATUS_SINGLE = 'S';
    const MARITAL_STATUS_MARRIED = 'M';
    const MARITAL_STATUS_DIVORCED = 'D';
    const MARITAL_STATUS_WIDOWED = 'W';
    const MARITAL_STATUS_UNKNOWN = 'U';

    public $gender_label;
    public $marital_status_label;
    public $name; // fullname for display reasons
    public $specialisation_ids;
    public $specialisation_labels;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%stteacher_registry}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['birthdate'], 'default', 'value' => null],
            [['birthdate'], 'date', 'format' => 'php:Y-m-d'],
            [['aei', 'tei', 'epal', 'iek', 'military_service_certificate', 'sign_language', 'braille'], 'default', 'value' => 0],
            [['aei', 'tei', 'epal', 'iek', 'military_service_certificate', 'sign_language', 'braille'], 'boolean'],
            ['specialisation_ids', 'each', 'rule' => ['integer']],
            [['protected_children'], 'integer', 'min' => 0, 'max' => '15'],
            [['comments', 'ama', 'efka_facility', 'municipality'], 'string'],
            [['gender', 'marital_status'], 'string', 'max' => 1],
            [['surname', 'firstname', 'fathername', 'mothername', 'city', 'tax_service', 'bank', 'birthplace'], 'string', 'max' => 100],
            [['mobile_phone', 'home_phone', 'work_phone'], 'string', 'max' => 40],
            [['home_address'], 'string', 'max' => 255],
            [['postal_code'], 'string', 'max' => 10],
            [['social_security_number'], 'match', 'pattern' => '/^[0-9]{11}$/'],
            [['tax_identification_number'], 'match', 'pattern' => '/^[0-9]{9}$/'],
            [['identity_number', 'passport_number'], 'string', 'max' => 30],
            [['identity_number'], 'default', 'value' => function ($model, $attribute) {
                return $this->passport_number;
            }], // if no identity card is provided, use the passport if available
            [['identity_number'], 'filter', 'filter' => function ($value) {
                // convert all latin-like characters, leave the rest
                return strtr(mb_strtoupper($value), [
                    // 'ΓΔΘΛΞΠΣΦΨΩ'
                    ' ' => '',
                    '-' => '',
                    'Α' => 'A',
                    'Β' => 'B',
                    'Ε' => 'E',
                    'Ζ' => 'Z', 
                    'Η' => 'H',
                    'Ι' => 'I',
                    'Κ' => 'K',
                    'Μ' => 'M',
                    'Ν' => 'N',
                    'Ο' => 'O',
                    'Ρ' => 'P', 
                    'Τ' => 'T',
                    'Υ' => 'Y',
                    'Χ' => 'X'
                ]);
            }],
            [['identity_number'], 'match', 'pattern' => \Yii::$app->getModule('SubstituteTeacher')->params['identity-number-pattern']],
            [['iban'], 'string', 'max' => 34],
            [['email'], 'email'],
            [['identity_number', 'passport_number'], 'unique'],
            [['social_security_number'], 'unique'],
            [['tax_identification_number'], 'unique'],
            [['gender'], 'in', 'range' => [self::GENDER_FEMALE, self::GENDER_MALE, self::GENDER_OTHER]],
            [['marital_status'], 'in', 'range' => [self::MARITAL_STATUS_SINGLE, self::MARITAL_STATUS_MARRIED, self::MARITAL_STATUS_DIVORCED, self::MARITAL_STATUS_WIDOWED, self::MARITAL_STATUS_UNKNOWN]],
            [
                'specialisation_ids', 'each', 'rule' => [
                    'exist', 'skipOnError' => true, 'targetClass' => Specialisation::className(), 'targetAttribute' => 'id'
                ]
            ],
            [
                [ 'specialisation_ids', //'birthdate', 'gender', 'mothername', 'city', 'tax_service',
                  //'birthplace', 'mobile_phone', 'home_address', 'postal_code',
                  'firstname', 'surname', 'fathername', 'protected_children',                  
                  'tax_identification_number', 'identity_number' ],
                // [ 'marital_status', 'social_security_number',  'bank', 'iban', 'email' ],
                'required'
            ],
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
            'specialisation_id' => Yii::t('substituteteacher', 'Specialisation'),
            'specialisation_ids' => Yii::t('substituteteacher', 'Specialisations'),
            'gender' => Yii::t('substituteteacher', 'Gender'),
            'surname' => Yii::t('substituteteacher', 'Surname'),
            'firstname' => Yii::t('substituteteacher', 'Firstname'),
            'fathername' => Yii::t('substituteteacher', 'Fathername'),
            'mothername' => Yii::t('substituteteacher', 'Mothername'),
            'marital_status' => Yii::t('substituteteacher', 'Marital Status'),
            'protected_children' => Yii::t('substituteteacher', 'Protected Children'),
            'mobile_phone' => Yii::t('substituteteacher', 'Mobile Phone'),
            'home_phone' => Yii::t('substituteteacher', 'Home Phone'),
            'work_phone' => Yii::t('substituteteacher', 'Work Phone'),
            'home_address' => Yii::t('substituteteacher', 'Home Address'),
            'city' => Yii::t('substituteteacher', 'City'),
            'postal_code' => Yii::t('substituteteacher', 'Postal Code'),
            'social_security_number' => Yii::t('substituteteacher', 'Social Security Number'),
            'tax_identification_number' => Yii::t('substituteteacher', 'Tax Identification Number'),
            'tax_service' => Yii::t('substituteteacher', 'Tax Service'),
            'identity_number' => Yii::t('substituteteacher', 'Identity Number'),
            'bank' => Yii::t('substituteteacher', 'Bank'),
            'iban' => Yii::t('substituteteacher', 'Iban'),
            'email' => Yii::t('substituteteacher', 'Email'),
            'birthdate' => Yii::t('substituteteacher', 'Birthdate'),
            'birthplace' => Yii::t('substituteteacher', 'Birthplace'),
            'aei' => Yii::t('substituteteacher', 'Aei'),
            'tei' => Yii::t('substituteteacher', 'Tei'),
            'epal' => Yii::t('substituteteacher', 'Epal'),
            'iek' => Yii::t('substituteteacher', 'Iek'),
            'military_service_certificate' => Yii::t('substituteteacher', 'Military Service Certificate'),
            'sign_language' => Yii::t('substituteteacher', 'Sign Language'),
            'braille' => Yii::t('substituteteacher', 'Braille'),
            'comments' => Yii::t('substituteteacher', 'Comments'),
            'ama' => Yii::t('substituteteacher', 'AMA'),
            'efka_facility' => Yii::t('substituteteacher', 'EFKA Facility'),
            'municipality' => Yii::t('substituteteacher', 'Registration Municipality'),
            'created_at' => Yii::t('substituteteacher', 'Created At'),
            'updated_at' => Yii::t('substituteteacher', 'Updated At'),
            'passport_number' => Yii::t('substituteteacher', 'Passport Number'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeachers()
    {
        return $this->hasMany(Teacher::className(), ['registry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeacherStatusAudits()
    {
        return $this->hasMany(TeacherStatusAudit::className(), ['teacher_id' => 'id'])
            ->via('teachers')
            ->orderBy([TeacherStatusAudit::tableName() . '.[[audit_ts]]' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialisations()
    {
        return $this->hasMany(Specialisation::className(), ['id' => 'specialisation_id'])
            ->viaTable('{{%stteacher_registry_specialisation}}', ['registry_id' => 'id'])
            ->from(['specialisations' => '{{%specialisation}}']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeacherRegistrySpecialisations()
    {
        return $this->hasMany(TeacherRegistrySpecialisation::className(), ['registry_id' => 'id']);
    }

    public static function defaultSelectables($index_property = 'id', $label_property = 'name', $group_property = null)
    {
        return static::selectables($index_property, $label_property, $group_property, null);
    }

    /**
     * Get a list of available choices in the form of
     * ID => LABEL suitable for select lists.
     *
     */
    public static function getChoices($for = 'gender')
    {
        $choices = [];
        if ($for === 'gender') {
            return [
                self::GENDER_FEMALE => Yii::t('substituteteacher', 'Female'),
                self::GENDER_MALE => Yii::t('substituteteacher', 'Male'),
                self::GENDER_OTHER => Yii::t('substituteteacher', 'Other'),
            ];
        } elseif ($for === 'marital_status') {
            return [
                self::MARITAL_STATUS_SINGLE => Yii::t('substituteteacher', 'Single'),
                self::MARITAL_STATUS_MARRIED => Yii::t('substituteteacher', 'Married'),
                self::MARITAL_STATUS_DIVORCED => Yii::t('substituteteacher', 'Divorced'),
                self::MARITAL_STATUS_WIDOWED => Yii::t('substituteteacher', 'Widowed'),
                self::MARITAL_STATUS_UNKNOWN => Yii::t('substituteteacher', 'Unknown'),
            ];
        }

        return $choices;
    }

    public function afterFind()
    {
        parent::afterFind();

        $this->name = "{$this->firstname} {$this->surname} ({$this->fathername})";

        $this->specialisation_labels = array_map(function ($m) {
            return $m->label;
        }, $this->specialisations);
        $this->specialisation_ids = array_map(function ($m) {
            return $m->id;
        }, $this->specialisations);


        switch ($this->gender) {
            case self::GENDER_FEMALE:
                $this->gender_label = Yii::t('substituteteacher', 'Female');
                break;
            case self::GENDER_MALE:
                $this->gender_label = Yii::t('substituteteacher', 'Male');
                break;
            case self::GENDER_OTHER:
                $this->gender_label = Yii::t('substituteteacher', 'Other');
                break;
            default:
                $this->gender_label = null;
                break;
        }

        switch ($this->marital_status) {
            case self::MARITAL_STATUS_DIVORCED:
                $this->marital_status_label = Yii::t('substituteteacher', 'Divorced');
                break;
            case self::MARITAL_STATUS_MARRIED:
                $this->marital_status_label = Yii::t('substituteteacher', 'Married');
                break;
            case self::MARITAL_STATUS_SINGLE:
                $this->marital_status_label = Yii::t('substituteteacher', 'Single');
                break;
            case self::MARITAL_STATUS_UNKNOWN:
                $this->marital_status_label = Yii::t('substituteteacher', 'Unknown');
                break;
            case self::MARITAL_STATUS_WIDOWED:
                $this->marital_status_label = Yii::t('substituteteacher', 'Widowed');
                break;
            default:
                $this->marital_status_label = null;
                break;
        }
    }

    /**
     * @inheritdoc
     * @return TeacherRegistryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TeacherRegistryQuery(get_called_class());
    }
}
