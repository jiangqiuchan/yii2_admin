<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UninstallFeedback;

/**
 * UninstallFeedbackSearch represents the model behind the search form about `common\models\UninstallFeedback`.
 */
class UninstallFeedbackSearch extends UninstallFeedback
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'integer'],
            [['reason', 'content', 'version'], 'safe'],
            [['reason', 'content', 'version'], 'trim'],
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
        $query = UninstallFeedback::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '15',
                //'route' => 'product-details/index'
            ],
            'sort' => [
                'attributes' => ['created_at','id'],
                'defaultOrder' => ['id' => SORT_DESC]
            ],
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
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'reason', $this->reason])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'version', $this->version]);
        
        if ((!empty($params['start_at1']) && !empty($params['end_at1'])) || (empty($params['start_at1']) && !empty($params['end_at1']))) {
            $startTime = empty($params['start_at1']) ? 0 : strtotime($params['start_at1']);
            $endTime = strtotime($params['end_at1']) + 86399;
            $query->andFilterWhere(['between', 'created_at', $startTime, $endTime]);
        }

        return $dataProvider;
    }
}
