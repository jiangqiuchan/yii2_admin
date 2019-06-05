<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\SoftPdfRefund;
use backend\components\commonality;

/**
 * SoftPdfRefundSearch represents the model behind the search form about `backend\models\SoftPdfRefund`.
 */
class SoftPdfRefundSearch extends SoftPdfRefund
{
    public $username;
    public $trade_no;
    public $package;
    public $channel;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'batch_num', 'state', 'admin_id', 'package'], 'integer'],
//             [['money'], 'number'],
            [['username'], 'string'],
            [['batch_no', 'reason', 'trade_no', 'channel','created_at'], 'safe'],
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
        $query = SoftPdfRefund::find()
            ->alias('r')
            ->select("r.*,o.trade_no,o.package,u.username,u.channel")
            ->joinWith('softPdfUser u')
            ->joinWith('softPdfOrder o')
            ->joinWith('admin a');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '15',
            ],
            'sort' => [
                'attributes' => ['user_id','trade_no','id','created_at'],
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
            'r.user_id' => $this->user_id,
            'admin_id' => $this->admin_id,
            'order_id' => $this->order_id,
            'trade_no' => $this->trade_no,
            'money' => $this->money,
            'batch_num' => $this->batch_num,
            'state' => $this->state,
//            'created_at' => $this->created_at,
            'package' => $this->package,
            'u.channel' => $this->channel,
        ]);

        if (!empty($params['SoftPdfRefundSearch']['created_at'])) {
            $dateArr = explode('到',$params['SoftPdfRefundSearch']['created_at']);
            if ($dateArr) {
                $startTime = strtotime($dateArr[0]);
                $endTime = strtotime($dateArr[1]) + 86399;
                $query->andFilterWhere(['between', 'r.created_at', $startTime, $endTime]);
            }
        }

        $query->andFilterWhere(['like', 'batch_no', $this->batch_no])
            ->andFilterWhere(['like', 'u.username', commonality::userTextEncode($this->username)])
            ->andFilterWhere(['like', 'reason', $this->reason]);

        return $dataProvider;
    }
}
