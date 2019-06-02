<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Channel;

/**
 * ChannelSearch represents the model behind the search form of `backend\models\Channel`.
 */
class ChannelSearch extends Channel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['channel_name', 'channel_url', 'soft_versions', 'channel_biaoshi', 'add_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = Channel::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'add_time' => $this->add_time,
        ]);

        $query->andFilterWhere(['like', 'channel_name', $this->channel_name])
            ->andFilterWhere(['like', 'channel_url', $this->channel_url])
            ->andFilterWhere(['like', 'soft_versions', $this->soft_versions])
            ->andFilterWhere(['like', 'channel_biaoshi', $this->channel_biaoshi]);

        return $dataProvider;
    }
}
