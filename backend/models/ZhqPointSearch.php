<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ZhqPoint;

/**
 * SoftPdfSearch represents the model behind the search form about `backend\models\SoftPdf`.
 */
class ZhqPointSearch extends ZhqPoint
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'is_new', 'step', 'time', 'err', 'user_id'], 'integer'],
            [['mac', 'channel', 'version'], 'safe'],
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
        $query = ZhqPoint::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'is_new' => $this->is_new,
//             'step' => $this->step,
            'time' => $this->time,
        ]);

        $query->andFilterWhere(['like', 'mac', $this->mac])
            ->andFilterWhere(['like', 'channel', $this->channel])
            ->andFilterWhere(['like', 'version', $this->version])
            ->andFilterWhere(['like', 'step', $this->step]);
        
        if (!empty($params['time'])) {
            $startTime = strtotime($params['time']);
            $endTime = $startTime + 86399;
            $query->andFilterWhere(['between', 'time', $startTime, $endTime]);
        }

        return $dataProvider;
    }
}
