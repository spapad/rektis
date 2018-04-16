<?php

namespace app\modules\schooltransport\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\schooltransport\models\SchtransportTransport;

/**
 * SchtransportTransportSearch represents the model behind the search form about `app\modules\schooltransport\models\SchtransportTransport`.
 */
class SchtransportTransportSearch extends SchtransportTransport
{
    public $meeting_country;
    public $meeting_city;
    public $school_name;
    public $meeting_startdate;
    public $meeting_enddate;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {    
        return [[['transport_id', 'meeting_id', 'school_id'], 'integer'],
                [['transport_submissiondate', 'transport_startdate', 'transport_enddate', 'transport_teachers', 
                'transport_students', 'transport_localdirectorate_protocol', 'transport_pde_protocol', 'transport_remarks', 
                'transport_datesentapproval', 'transport_dateprotocolcompleted', 'transport_approvalfile',                     
                'transport_signedapprovalfile'], 'safe']];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        //$query = SchtransportTransport::find();
        
        $tblprefix = Yii::$app->db->tablePrefix;
        $query = (new \yii\db\Query())
                    ->select($tblprefix . 'schtransport_transport.*,' . $tblprefix . 'schtransport_meeting.*,' . 
                             $tblprefix . 'schoolunit.*,'. $tblprefix . 'schtransport_program.*')
                    ->from($tblprefix . 'schtransport_transport,' . $tblprefix . 'schtransport_meeting,' . 
                           $tblprefix . 'schoolunit,' . $tblprefix . 'schtransport_program')
                    ->where($tblprefix . 'schtransport_transport.meeting_id  = ' . $tblprefix . 'schtransport_meeting.meeting_id')
                    ->andWhere($tblprefix . 'schtransport_transport.school_id  = ' . $tblprefix . 'schoolunit.school_id')
                    ->andWhere($tblprefix . 'schtransport_meeting.program_id = ' . $tblprefix . 'schtransport_program.program_id');
        //echo $query->createCommand()->rawSql; die();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [ 'attributes' => ['school_name', 'meeting_country', 'meeting_city', 'transport_startdate', 'transport_enddate',
                                         'meeting_startdate', 'meeting_enddate'],
                        'defaultOrder' => ['transport_startdate'=>SORT_ASC, 'school_name'=>SORT_ASC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'transport_id' => $this->transport_id,
            'transport_startdate' => $this->transport_startdate,
            'transport_enddate' => $this->transport_enddate
        ]);

        $query->andFilterWhere(['like', 'meeting_country', $this->meeting_country])
                ->andFilterWhere(['like', 'meeting_city', $this->meeting_city])
                ->andFilterWhere(['like', 'school_name', $this->school_name]);

        return $dataProvider;
    }
}
