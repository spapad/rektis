<?php

namespace app\modules\schooltransport\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SchtransportProgramSearch represents the model behind the search form about `app\modules\schooltransport\models\SchtransportProgram`.
 */
class SchtransportProgramSearch extends SchtransportProgram
{
    public $programcategory_programalias;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['program_id', 'programcategory_id'], 'integer'],
            [['program_title', 'program_code', 'programcategory_programalias'], 'safe'],
        ];
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
        $tblprefix = Yii::$app->db->tablePrefix;
        $transport_programcategs = $tblprefix . 'schtransport_programcategory';
        $transport_programs = $tblprefix . 'schtransport_program';
        $query = (new \yii\db\Query())
            ->select($transport_programcategs . '.*,' . $transport_programs . '.*')
            ->from($transport_programcategs . ',' . $transport_programs)
            ->where($transport_programcategs . '.programcategory_id=' . $transport_programs . '.programcategory_id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [ 'attributes' => ['program_code', 'program_title', 'programcategory_programalias'],
                'defaultOrder' => ['programcategory_programalias'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'program_id' => $this->program_id,
            'programcategory_id' => $this->programcategory_id,
        ]);

        $query->andFilterWhere(['like', 'program_title', $this->program_title])
            ->andFilterWhere(['like', 'program_code', $this->program_code])
            ->andFilterWhere(['like', 'programcategory_programalias', $this->programcategory_programalias]);

        return $dataProvider;
    }
}
